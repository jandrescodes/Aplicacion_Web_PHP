<!doctype html>
<html lang="es">

<head>
  <title>Login - Sistema Web</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-iYQeCzEYFbKjA/T2uDLTpkwGzCiq6soy8tYaI1GyVh/UjpbCx/TYkiZhlZB6+fzT" crossorigin="anonymous">
  <link rel="stylesheet" type="text/css" href="<?= $public_base; ?>css/style.css">
  <link rel="icon" type="image/x-icon" href="<?= $public_base; ?>img/deadpool.ico">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" integrity="sha384-XGjxtQfXaH2tnPFa9x+ruJTuLE3Aa6LhHSWRr1XeTyhezb4abCG4ccI5AkVDxqC+" crossorigin="anonymous">
</head>

<body class="login-body">
  <main class="container-login">
    <div class="card" id="tarjeta">
      <div class="card-body">
        <div class="text-center mb-4">
          <h1 class="fw-bold text-dark h2 mb-0">BIENVENIDO</h1>
          <p class="text-muted small">Ingrese sus credenciales</p>
        </div>

        <?php if (!empty($mensaje)) : ?>
          <div class="alert alert-danger text-center py-2 mb-3" role="alert" aria-live="assertive">
            <small><?= htmlspecialchars((string)$mensaje, ENT_QUOTES, 'UTF-8'); ?></small>
          </div>
        <?php endif; ?>

        <form action="<?= htmlspecialchars((string)$formAction, ENT_QUOTES, 'UTF-8'); ?>" method="post">
          <input type="hidden" name="csrf_token" value="<?= htmlspecialchars((string)$csrfToken, ENT_QUOTES, 'UTF-8'); ?>">
          <div class="mb-3">
            <label for="usuario" class="form-label small fw-bold">Usuario</label>
            <div class="input-group">
              <span class="input-group-text"><i class="bi bi-person-circle" aria-hidden="true"></i></span>
              <input type="text" class="form-control" name="usuario" id="usuario" placeholder="Usuario" autocomplete="username" required autofocus>
            </div>
          </div>

          <div class="mb-4">
            <label for="password" class="form-label small fw-bold">Contraseña</label>
            <div class="input-group">
              <span class="input-group-text"><i class="bi bi-lock-fill" aria-hidden="true"></i></span>
              <input type="password" class="form-control" name="password" id="password" placeholder="Contraseña" autocomplete="current-password" required>
              <button type="button" class="input-group-text" id="togglePassword" aria-label="Mostrar contraseña" aria-pressed="false">
                <i class="bi bi-eye-fill" aria-hidden="true"></i>
              </button>
            </div>
          </div>

          <?php if (($rememberEnabled ?? 'true') === 'true') : ?>
            <div class="mb-3 form-check">
              <input type="checkbox" class="form-check-input" id="remember" name="remember" value="1">
              <label class="form-check-label small" for="remember">Recuérdame</label>
            </div>
          <?php endif; ?>

          <div class="d-grid">
            <button type="submit" class="btn btn-primary py-2 fw-bold shadow-sm">INICIAR SESIÓN</button>
          </div>
        </form>
      </div>
    </div>

    <!-- Animaciones -->
    <div class="square" aria-hidden="true"></div>
    <div class="square" aria-hidden="true"></div>
    <div class="square" aria-hidden="true"></div>
    <div class="circle" aria-hidden="true"></div>
    <div class="circle" aria-hidden="true"></div>
    <div class="circle" aria-hidden="true"></div>
    <div class="triangle" aria-hidden="true"></div>
    <div class="triangle" aria-hidden="true"></div>
    <div class="triangle" aria-hidden="true"></div>
  </main>

  <script src="https://code.jquery.com/jquery-3.7.0.min.js" integrity="sha384-NXgwF8Kv9SSAr+jemKKcbvQsz+teULH/a5UNJvZc6kP47hZgl62M1vGnw6gHQhb1" crossorigin="anonymous"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/animejs/3.2.1/anime.min.js" integrity="sha384-fXdIufVbE9aU7STmdk/DWK0imNOozId9fTwzM/gi0NfPjphEIC3gq0M760UnsKVy" crossorigin="anonymous"></script>
  <script src="<?= $public_base; ?>js/main.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-u1OknCvxWvY5kfmNBILK2hRnQC3Pr17a+RTT6rIHI7NnikvbZlHgTPOOmMi466C8" crossorigin="anonymous"></script>
</body>

</html>