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
    .btn-rais {
      text-decoration: none;
      padding: 12px 24px;
      background-color: #006CE0;
      border-radius: 20px;
      font-size: 14px;
      font-weight: 600;
      cursor: pointer;
    }
  </style>

</head>

<body>

  <div class="body">
    <div class="container">
      <div class="content">
        <h2>Registro de Actividades de Investigación de San Marcos - RAIS</h4>
        <h3>Estimado {{ $investigador->nombres }}:</h3>
        <p>
          Le recordamos que su constancia de docente investigador vigente vence el <strong>{{ $investigador->fecha_fin }}</strong>, recuerde 
          que ya puede solicitar una nueva constancia en el <a href="https://rais.unmsm.edu.pe">RAIS</a> dirigiéndose 
          a la opción de su perfil.
        </p>
        <br>
        <a class="btn-rais" href="https://rais.unmsm.edu.pe" style="color: #FFFFFF;">Ir al RAIS</a>
      </div>
    </div>
  </div>
</body>

</html>
