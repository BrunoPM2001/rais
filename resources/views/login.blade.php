<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Lineas de investigación</title>
  @vite(['resources/scss/app.scss', 'resources/js/app.js'])
</head>

<body>
  <div class="container">
    <div class="row vh-100 justify-content-center align-items-center">
      <div class="col-sm-6">
        <form action="{{ route('login_form') }}" method="post" class="card p-4">
          @csrf
          <h5>RAIS</h5>
          <div class="mb-3">
            <label for="username_mail" class="form-label">Usuario o email</label>
            <input type="text" class="form-control" id="username_mail" name="username_mail" aria-describedby="help">
            <div id="help" class="form-text">No compartas tus credenciales de acceso con nadie más.</div>
          </div>
          <div class="mb-3">
            <label for="password" class="form-label">Contraseña</label>
            <input type="password" class="form-control" id="password" name="password">
          </div>
          <div class="mb-3 form-check">
            <input type="checkbox" class="form-check-input" id="recuerdame">
            <label class="form-check-label" for="recuerdame">Recuérdame</label>
          </div>
          <div class="d-grid">
            <button type="submit" class="btn btn-primary">Ingresar</button>
          </div>
          @if ($errors->any())
          <div class="alert alert-danger mt-4">
            <ul class="m-0">
              @foreach ($errors->all() as $error)
              <li>{{ $error }}</li>
              @endforeach
            </ul>
          </div>
          @endif
        </form>
      </div>
    </div>
  </div>
</body>