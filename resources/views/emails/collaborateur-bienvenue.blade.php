<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Bienvenue chez Maison du Web</title>
</head>
<body style="margin:0;padding:0;background-color:#f4f4f5;font-family:'Segoe UI',Arial,sans-serif;">

  <table width="100%" cellpadding="0" cellspacing="0" style="background:#f4f4f5;padding:40px 0;">
    <tr>
      <td align="center">
        <table width="600" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:16px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,0.08);">

          <!-- Header -->
          <tr>
            <td style="background:linear-gradient(135deg,#f97316 0%,#ea580c 100%);padding:40px 40px 32px;text-align:center;">
              <h1 style="margin:0;color:#ffffff;font-size:28px;font-weight:700;">Maison du Web</h1>
              <p style="margin:8px 0 0;color:rgba(255,255,255,0.85);font-size:15px;">Plateforme RH — Accès collaborateur</p>
            </td>
          </tr>

          <!-- Corps -->
          <tr>
            <td style="padding:40px;">

              <p style="margin:0 0 24px;font-size:17px;color:#1f2937;font-weight:600;">
                Bonjour {{ $prenom }} {{ $nom }},
              </p>

              <p style="margin:0 0 24px;font-size:15px;color:#4b5563;line-height:1.6;">
                Votre compte collaborateur a été créé sur la plateforme <strong>Maison du Web</strong>.
                Voici vos identifiants de connexion :
              </p>

              <!-- Carte identifiants -->
              <table width="100%" cellpadding="0" cellspacing="0" style="background:#f8fafc;border:2px solid #e2e8f0;border-radius:12px;margin-bottom:28px;">
                <tr>
                  <td style="padding:28px;">
                    <table width="100%" cellpadding="0" cellspacing="0">
                      <tr>
                        <td style="padding:8px 0;border-bottom:1px solid #e2e8f0;">
                          <span style="font-size:13px;color:#9ca3af;font-weight:500;text-transform:uppercase;">Rôle</span><br/>
                          <span style="font-size:15px;color:#1f2937;font-weight:600;margin-top:4px;display:block;">{{ ucfirst($role) }}</span>
                        </td>
                      </tr>
                      <tr>
                        <td style="padding:8px 0;border-bottom:1px solid #e2e8f0;">
                          <span style="font-size:13px;color:#9ca3af;font-weight:500;text-transform:uppercase;">Email</span><br/>
                          <span style="font-size:15px;color:#1f2937;font-weight:600;margin-top:4px;display:block;">{{ $email }}</span>
                        </td>
                      </tr>
                      <tr>
                        <td style="padding:8px 0;">
                          <span style="font-size:13px;color:#9ca3af;font-weight:500;text-transform:uppercase;">Mot de passe temporaire</span><br/>
                          <span style="font-size:18px;color:#f97316;font-weight:700;margin-top:4px;display:block;letter-spacing:2px;font-family:monospace;">{{ $motDePasse }}</span>
                        </td>
                      </tr>
                    </table>
                  </td>
                </tr>
              </table>

              <!-- Avertissement -->
              <table width="100%" cellpadding="0" cellspacing="0" style="background:#fff7ed;border:1px solid #fed7aa;border-radius:10px;margin-bottom:28px;">
                <tr>
                  <td style="padding:16px 20px;">
                    <p style="margin:0;font-size:14px;color:#9a3412;">
                      ⚠️ <strong>Important :</strong> Ce mot de passe est temporaire. Vous serez invité à le modifier lors de votre première connexion.
                    </p>
                  </td>
                </tr>
              </table>
              <!-- Séparateur -->
              <hr style="border:none;border-top:2px dashed #e5e7eb;margin:0 0 28px;" />

              <!-- Section QR Code signature -->
              <table width="100%" cellpadding="0" cellspacing="0" style="background:#f0f9ff;border:2px solid #bae6fd;border-radius:12px;margin-bottom:28px;">
                <tr>
                  <td style="padding:28px;text-align:center;">

                    <p style="margin:0 0 6px;font-size:16px;font-weight:700;color:#0c4a6e;">
                       Enregistrez votre signature électronique
                    </p>
                    <p style="margin:0 0 20px;font-size:14px;color:#0369a1;line-height:1.6;">
                      Scannez ce QR Code avec votre téléphone pour dessiner et enregistrer
                      votre signature. Elle sera utilisée pour signer vos documents.
                    </p>

                    <!-- QR Code via api.qrserver.com -->
                    <img
                      src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data={{ urlencode($signatureUrl) }}"
                      alt="QR Code Signature"
                      width="200"
                      height="200"
                      style="border-radius:12px;border:3px solid #e0f2fe;display:block;margin:0 auto 20px;background:#fff;padding:8px;"
                    />

                    <p style="margin:0 0 8px;font-size:12px;color:#64748b;">
                      Ou ouvrez ce lien directement sur votre téléphone :
                    </p>
                    <a href="{{ $signatureUrl }}"
                       style="font-size:12px;color:#0369a1;word-break:break-all;">
                      {{ $signatureUrl }}
                    </a>

                    <table width="100%" cellpadding="0" cellspacing="0" style="margin-top:16px;">
                      <tr>
                        <td style="padding:8px 12px;background:#fff;border:1px solid #bae6fd;border-radius:8px;font-size:12px;color:#0369a1;text-align:center;">
                          ℹ️ Ce lien est à usage unique — il expirera après utilisation
                        </td>
                      </tr>
                    </table>

                  </td>
                </tr>
              </table>

              <p style="margin:0;font-size:14px;color:#9ca3af;line-height:1.6;">
                Si vous avez des questions, contactez votre responsable RH.
              </p>

            </td>
          </tr>

          <!-- Footer -->
          <tr>
            <td style="background:#f8fafc;padding:24px 40px;text-align:center;border-top:1px solid #e5e7eb;">
              <p style="margin:0;font-size:13px;color:#9ca3af;">
                © {{ date('Y') }} Maison du Web — Tous droits réservés
              </p>
            </td>
          </tr>

        </table>
      </td>
    </tr>
  </table>

</body>
</html>