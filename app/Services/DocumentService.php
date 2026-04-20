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


        if (!empty($data['user_ids']) && is_array($data['user_ids'])) {
            foreach ($data['user_ids'] as $userId) {
                $this->assignDocument($document->id, (int) $userId);
            }
        }

        return $document;
    }


    
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


        if (!empty($data['user_ids']) && is_array($data['user_ids'])) {

            $existingUserIds = $document->assignments()->pluck('user_id')->toArray();


            foreach ($data['user_ids'] as $userId) {
                if (!in_array($userId, $existingUserIds)) {
                    $this->assignDocument($document->id, (int) $userId);
                }
            }


            $document->assignments()
                ->whereNotIn('user_id', $data['user_ids'])
                ->delete();
        }

        return $document;
    }


    
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
            'signed_at'   => null, 
        ]);
    }



    private function updateAssignment(int $documentId, int $userId, array $data)
    {
        $assignment = DocumentAssignment::where('document_id', $documentId)
            ->where('user_id', $userId)
            ->first();

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

            if ($data['status'] === 'signed') {
                $assignment->signed_at = now();
            }
        }

        $assignment->save();

        return $assignment;
    }

    public function getDocumentsForCollaborateur(int $userId, array $filters = [])
    {
        $user = Auth::user();

        $query = Document::with('assignments.collaborateur', 'assignments.assignedBy');

        if (strtolower($user->role->name) === 'collaborateur') {
            $query->whereHas('assignments', function ($q) use ($userId) {
                $q->where('user_id', $userId);
            });
        }

        if (!empty($filters['namedoc'])) {
            $query->where('namedoc', 'ilike', '%' . $filters['namedoc'] . '%');
        }

        return $query->orderBy('created_at', 'desc')->get()->map(function ($doc) {
            return [
                'id'            => $doc->id,
                'namedoc'       => $doc->namedoc,

                'path'          => Storage::url($doc->path),

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


                    'signed_at'     => optional($a->signed_at)->toDateTimeString(),

                    'signature_path'=> $a->signaturePath(),
                ]),
            ];
        });
    }
}