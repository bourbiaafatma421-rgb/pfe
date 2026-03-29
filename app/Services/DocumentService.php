<?php

namespace App\Services;

use App\Models\Document;
use App\Models\DocumentAssignment;
use App\Exceptions\Document\DocumentAlreadyExistsException;
use App\Exceptions\Document\DocumentNotFoundException;
use App\Exceptions\Document\DocumentDeletionException;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class DocumentService
{
    // ─── Lister les documents ─────────────────────────────────────────────────

    public function listDocuments(array $filters = [])
    {
        $query = Document::with('assignments.collaborateur', 'assignments.assignedBy');

        if (!empty($filters['namedoc'])) {
            $query->where('namedoc', 'ilike', "%{$filters['namedoc']}%");
        }

        $documents = $query->orderBy('created_at', 'desc')->get();

        return $documents->map(function ($doc) {
            return [
                'id'            => $doc->id,
                'namedoc'       => $doc->namedoc,
                'path'          => asset('storage/' . $doc->path),
                'signature_req' => $doc->signature_req,
                'assignments'   => $doc->assignments->map(fn($a) => [
                    'user_id'       => $a->user_id,
                    'user_fullname' => $a->collaborateur
                        ? $a->collaborateur->first_name . ' ' . $a->collaborateur->last_name
                        : 'Inconnu',
                    'assigned_by'   => $a->assignedBy
                        ? $a->assignedBy->first_name . ' ' . $a->assignedBy->last_name
                        : 'Système',
                    'status'        => $a->status,
                ]),
            ];
        });
    }

    // ─── Créer un document + multi-assignation ────────────────────────────────

    public function createDocument(array $data)
    {
        if (Document::where('namedoc', $data['namedoc'])->exists()) {
            throw new DocumentAlreadyExistsException();
        }

        $document = Document::create([
            'namedoc'       => $data['namedoc'],
            'path'          => $data['path'],
            'signature_req' => $data['signature_req'],
        ]);

        // ─── Multi-assignation ────────────────────────────────────────────────
        if (!empty($data['user_ids']) && is_array($data['user_ids'])) {
            foreach ($data['user_ids'] as $userId) {
                $this->assignDocument($document->id, (int) $userId);
            }
        }

        return $document;
    }

    // ─── Mettre à jour un document ────────────────────────────────────────────

    public function updateDocument(int $id, array $data)
    {
        $document = Document::findOrFail($id);

        if (!empty($data['path']) && $data['path'] instanceof \Illuminate\Http\UploadedFile) {
            if ($document->path && Storage::disk('public')->exists($document->path)) {
                Storage::disk('public')->delete($document->path);
            }
            $document->path = $data['path']->store('documents', 'public');
        }

        if (array_key_exists('namedoc', $data)) {
            $document->namedoc = $data['namedoc'];
        }

        if (array_key_exists('signature_req', $data)) {
            $document->signature_req = filter_var($data['signature_req'], FILTER_VALIDATE_BOOLEAN);
        }

        $document->save();

        // Mise à jour des assignations si user_ids fournis
        if (!empty($data['user_ids']) && is_array($data['user_ids'])) {
            // Supprimer les anciennes assignations
            $document->assignments()->delete();
            // Recréer les nouvelles
            foreach ($data['user_ids'] as $userId) {
                $this->assignDocument($document->id, (int) $userId);
            }
        }

        return $document;
    }

    // ─── Supprimer un document ────────────────────────────────────────────────

    public function deleteDocument(int $id)
    {
        $document = Document::find($id);
        if (!$document) {
            throw new DocumentNotFoundException();
        }

        try {
            if ($document->path && Storage::disk('public')->exists($document->path)) {
                Storage::disk('public')->delete($document->path);
            }
            $document->assignments()->delete();
            $document->delete();
            return $document;
        } catch (\Exception $e) {
            throw new DocumentDeletionException("Impossible de supprimer le document: " . $e->getMessage());
        }
    }

    // ─── Assigner un document à un utilisateur ────────────────────────────────

    private function assignDocument(int $documentId, int $userId, string $status = 'pending')
    {
        $user = User::find($userId);
        if (!$user) {
            throw new \Exception("Utilisateur destinataire introuvable (id: $userId).");
        }

        $assignedBy = Auth::user();

        return DocumentAssignment::create([
            'document_id' => $documentId,
            'user_id'     => $user->id,
            'assigned_by' => $assignedBy->id,
            'status'      => $status,
        ]);
    }

    // ─── Mettre à jour une assignation ───────────────────────────────────────

    private function updateAssignment(int $documentId, array $data)
    {
        $assignment = DocumentAssignment::where('document_id', $documentId)->first();
        if (!$assignment) return null;

        if (!empty($data['user_id'])) {
            $user = User::find($data['user_id']);
            if (!$user) {
                throw new \Exception("Utilisateur destinataire introuvable.");
            }
            $assignment->user_id = $user->id;
        }

        $assignment->assigned_by = Auth::id();

        if (!empty($data['status'])) {
            $assignment->status = $data['status'];
        }

        $assignment->save();
        return $assignment;
    }
}