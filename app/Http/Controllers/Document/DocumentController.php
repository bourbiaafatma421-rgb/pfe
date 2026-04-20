<?php

namespace App\Http\Controllers\Document;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\DocumentService;
use App\Http\Requests\Document\AjoutDocumentRequest;
use App\Http\Requests\Document\UpdateDocumentRequest;
use App\Models\Document;
use App\Exceptions\Document\DocumentNotFoundException;
use App\Exceptions\Document\DocumentDeletionException;
use App\Http\Requests\Document\SignDocumentRequest;
use Illuminate\Support\Facades\Log;

class DocumentController extends Controller
{
    protected DocumentService $service;

    public function __construct(DocumentService $service)
    {
        $this->service = $service;
    }


    
    public function store(AjoutDocumentRequest $request)
    {
        $this->authorize('create', Document::class);

        try {
            $data = $request->validated();


            if ($request->hasFile('path')) {
                $data['path'] = $request->file('path')->store('documents', 'public');
            }


            $document = $this->service->createDocument($data);
            $document->load('assignments.collaborateur');

            return response()->json([
                'message'  => 'Document créé avec succès',
                'document' => [
                    'id'            => $document->id,
                    'namedoc'       => $document->namedoc,
                    'path'          => asset('storage/' . $document->path),
                    'signature_req' => $document->signature_req,
                    'assignments'   => $document->assignments->map(fn($a) => [
                        'user_id'   => $a->user_id,
                        'user_name' => $a->collaborateur
                            ? $a->collaborateur->first_name . ' ' . $a->collaborateur->last_name
                            : 'Inconnu',
                        'status'    => $a->status,
                    ]),
                ]
            ], 201);

        } catch (\Throwable $e) {
            Log::error('Erreur création document', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);

            return response()->json([
                'message' => 'Erreur lors de la création du document',
                'erreur'  => $e->getMessage()
            ], 500);
        }
    }



    public function update(UpdateDocumentRequest $request, int $id)
    {
        $document = Document::findOrFail($id);
        $this->authorize('update', $document);

        $data = $request->validated();

        if ($request->hasFile('path')) {
            $data['path'] = $request->file('path');
        }

        if ($request->filled('status')) {
            $data['status'] = $request->input('status');
        }

        $document = $this->service->updateDocument($id, $data);
        $document->load('assignments.collaborateur', 'assignments.assignedBy');

        return response()->json([
            'message'  => 'Document mis à jour avec succès',
            'document' => [
                'id'            => $document->id,
                'namedoc'       => $document->namedoc,
                'path'          => asset('storage/' . $document->path),
                'signature_req' => $document->signature_req,
                'assignments'   => $document->assignments->map(fn($a) => [
                    'user_id'   => $a->user_id,
                    'user_name' => $a->collaborateur
                        ? $a->collaborateur->first_name . ' ' . $a->collaborateur->last_name
                        : 'Inconnu',
                    'status'    => $a->status,
                ]),
            ]
        ]);
    }



    public function destroy(int $id)
    {
        $this->authorize('delete', Document::class);

        try {
            $this->service->deleteDocument($id);
            return response()->json(['message' => 'Document supprimé avec succès']);
        } catch (DocumentNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        } catch (DocumentDeletionException $e) {
            return response()->json(['message' => 'Erreur lors de la suppression du document'], 500);
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Erreur inconnue', 'erreur' => $e->getMessage()], 500);
        }
    }



    public function index(Request $request)
    {
        $this->authorize('viewAny', Document::class);

        $filters   = $request->only('namedoc');
        $documents = $this->service->listDocuments($filters);

        return response()->json(['documents' => $documents]);
    }



    public function view(int $id)
    {
        $document = Document::findOrFail($id);
        $this->authorize('view', $document);

        $path = storage_path('app/public/' . $document->path);

        if (!file_exists($path)) {
            return response()->json(['message' => 'Fichier introuvable'], 404);
        }

        return response()->file($path, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $document->namedoc . '.pdf"',
        ]);
    }


    public function mesDocuments(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Non authentifié'], 401);
        }
        
        $this->authorize('viewOwn', Document::class);
        $filters = $request->only('namedoc');

        $documents = $this->service->getDocumentsForCollaborateur($user->id, $filters);

        return response()->json([
            'documents' => $documents
        ]);
    }
}