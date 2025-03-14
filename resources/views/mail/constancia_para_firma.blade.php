<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>Correo importante</title>

  <style>
    .body {
      width: 100%;
      margin: 0;
      padding: 0;
      background-color: #F1F1F1;
    }

    .container {
      max-width: 600px;
      margin: 0 auto;
      background-color: #FFFFFF;
    }

    .content {
      padding: 20px;
      text-align: center;
      font-family: Arial, sans-serif;
      font-size: 14px;
      line-height: 1.5;
      color: #333333;
    }
  </style>

</head>

<body>

  <div class="body">
    <div class="container">
      <div class="content">
        <h2>Registro de Actividades de Investigación de San Marcos - RAIS</h4>
          <p>Se remite documento para firma solicitado por {{ $nombre }}, remitir el documento a
            <strong>{{ $email }}</strong>
          </p>
          <p>
            Tipo de documento: <strong>Constancia</strong>
          </p>
      </div>
    </div>
  </div>
</body>

</html>
