<?php

namespace App\Http\Controllers;

use App\Http\Requests\ChangePasswordRequest;
use App\Services\ChangePasswordService;
use App\Exceptions\Auth\UserNotAuthenticatedException;
use App\Exceptions\Auth\PasswordAlreadyChangedException;


class ChangePasswordController extends Controller
{
protected ChangePasswordService $service;

    public function __construct(ChangePasswordService $service)
    {
        $this->service = $service;
    }

    public function setPassword(ChangePasswordRequest $request)
    {
        try {   
            $this->service->setPassword(
                $request->user(),
                $request->new_password
            );

            return response()->json([
                'message' => 'Mot de passe défini avec succès'
            ], 200);

        } catch (UserNotAuthenticatedException $e) {
            return response()->json(['message' => $e->getMessage()], 401);

        } catch (PasswordAlreadyChangedException $e) {
            return response()->json(['message' => $e->getMessage()], 403);

        } catch (InvalidUserException $e) {
            return response()->json(['message' => $e->getMessage()], 400);

        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Erreur serveur'
            ], 500);
        }
   
    }
}
