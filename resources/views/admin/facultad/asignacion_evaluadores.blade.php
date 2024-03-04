<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <meta name="csrf_token" content="{{ csrf_token() }}" />
  <title>Asignación de evaluadores</title>
  @vite(['resources/scss/app.scss', 'resources/js/app.js'])
  <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">

</head>

<body>
  @include('admin.components.navbar')
  <div class="text-bg-secondary">
    <p class="container-fluid"><strong>Evaluadores proyectos</strong></p>
  </div>

  <div class="container mb-4">
    <!--  Tab list  -->
    <ul class="nav nav-tabs" id="myTab" role="tablist">
      <li class="nav-item" role="presentation">
        <button class="nav-link active" id="listar-tab" data-bs-toggle="tab" data-bs-target="#listar-tab-pane" type="button" role="tab" aria-controls="listar-tab-pane" aria-selected="true">Listado</button>
      </li>
    </ul>

    <div class="tab-content border border-top-0 rounded-bottom" id="myTabContent">
      <div class="tab-pane fade show active p-4" id="listar-tab-pane" role="tabpanel" aria-labelledby="listar-tab" tabindex="0">
        <!--Filtro 1-->
        <form class="row mb-4">
          <div class="col-sm-2">
            <select id="opciones" class="form-select">
              <option value="opcion1" selected>Sin evaluador</option>
              <option value="opcion2">Con evaluador</option>
              <option value="opcion3">Todos</option>
            </select>
          </div>
          <div class="col-sm-6">
            <div class="row">
              <div class="col">
                <div class="d-flex justify-content-start">
                  <button id="botonA" type="button" class="btn btn-primary">Asignar evaluador</button>
                  <button id="botonB" type="button" class="btn btn-info ml-2" style="display: none;">Editar evaluador</button>
                </div>
              </div>
            </div>
          </div>
        </form>


        <!--  Tabla  -->
        <div class="overflow-x-hidden">
          <table id="table" class="table table-striped table-hover align-middle" style="width:100%">
            <thead>
              <tr>
                <th>
                  ID
                </th>
                <th>
                  Tipo
                </th>
                <th>
                  Linea
                </th>
                <th>
                  Facultad del grupo
                </th>
                <th>
                  Título
                </th>
                <th>
                  Evaluadores
                </th>
              </tr>
            </thead>
            <tbody>
            </tbody>
            <tfoot>
              <tr>
                <th>
                  ID
                </th>
                <th>
                  Tipo
                </th>
                <th>
                  Linea
                </th>
                <th>
                  Facultad del grupo
                </th>
                <th>
                  Título
                </th>
                <th>
                  Evaluadores
                </th>
              </tr>
            </tfoot>
          </table>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal para editar -->
  @extends('admin.components.modal')
  @section('form')
  <form id="updateForm">
    @endsection

    @section('titulo')
    Editar evaluador
    @endsection

    @section('contenido')
    <input type="text" hidden id="edit_id" name="edit_id">
    <input type="text" hidden id="tabla_id" name="tabla_id">
    <div class="row align-items-center mb-3">
      <label for="edit_evaluador1" class="col-sm-4 col-form-label">Evaluador 1</label>
      <div class="col-sm-8">
        <input type="text" id="edit_evaluador1" name="edit_evaluador1" class="form-control">
      </div>
    </div>

   <div class="row align-items-center mb-3">
      <label for="edit_evaluador2" class="col-sm-4 col-form-label">Evaluador 2</label>
      <div class="col-sm-8">
        <input type="text" id="edit_evaluador2" name="edit_evaluador2" class="form-control">
      </div>
    </div>

   <div class="row align-items-center mb-3">
      <label for="edit_evaluador3" class="col-sm-4 col-form-label">Evaluador 3</label>
      <div class="col-sm-8">
        <input type="text" id="edit_evaluador3" name="edit_evaluador3" class="form-control">
      </div>
    </div>
    
    @endsection

    @section('end_form')
  </form>
  @endsection

  <!--  Toast para notificaciones -->
  @extends('admin.components.toast')

  <script type="module">
    $(document).ready(function() {
    //  Iniciar modal y toast
    let modal = new bootstrap.Modal(document.getElementById('myModal'), {});
    let toast = new bootstrap.Toast(document.getElementById('myToast'));

    function mostrarBotones() {
        var opciones = $("#opciones");
        var botonA = $("#botonA");
        var botonB = $("#botonB");

        // Ocultar todos los botones al principio
        botonA.show()
        botonB.hide()

        // Mostrar los botones según la opción seleccionada
        if (opciones.val() === "opcion1") {
          botonA.show()
          botonB.hide()
        } else if (opciones.val() === "opcion2") {
            botonA.hide()
            botonB.show()
        } else if (opciones.val() === "opcion3") {
            botonA.show()
            botonB.show()
        }
    }
    // Llamar a la función al cargar la página
    //window.onload = mostrarBotones;
    $("#opciones").on("change",mostrarBotones)

    // Función de validación al hacer clic en "Editar evaluador"
    function editarEvaluador() {
        var table = $('#table').DataTable();
        var selectedRows = table.rows({ selected: true }).count();

        if (selectedRows === 1) {
          // Aquí colocas la lógica para editar evaluador
          console.log("Editar evaluador para una fila seleccionada.");
          modal.show()
        } else {
          alert("Por favor, seleccione solo un proyecto con evaluadores asigandos.");
        }
    }

    // Función de validación al hacer clic en "Asignar evaluador"
    function asignarEvaluador() {
        var table = $('#table').DataTable();
        var selectedRows = table.rows({ selected: true }).count();

        if (selectedRows === 0) {
          alert("Seleccione al menos un proyecto.");
        } else {
          // Aquí colocas la lógica para asignar evaluador
          console.log("Asignar evaluador para una fila seleccionada.");
        }
    }

    $("#botonA").on("click",asignarEvaluador)
    $("#botonB").on("click",editarEvaluador)


    });
  </script>


  <script type="module">
    $(document).ready(function() {
      //  Iniciar tabla, toast y modal
      let toast = new bootstrap.Toast(document.getElementById('myToast'));
      //  Datatable
      let ajax_url = 'http://localhost:8000/api/admin/facultad/getAllEvaluadores'
      let table = new DataTable('#table', {
        paging: true,
        pagingType: 'full_numbers',
        deferRender: true,
        processing: true,
        lengthChange: false,
        scrollX: true,
        ajax: ajax_url,
        columns: [{
            data: 'id'
          },
          {
            data: 'tipo_proyecto'
          },
          {
            data: 'linea'
          },
          {
            data: 'facultad'
          },
          {
            data: 'titulo'
          },
          {
            data: 'evaluadores'
          },
        ],
        //  Idioma dela información mostrada
        language: {
          zeroRecords: "No se encontraron resultados",
          info: "Mostrando _START_-_END_ de _TOTAL_ registros.",
          infoEmpty: "No hay registros ...",
          infoFiltered: "(filtrado de _MAX_ registros)",
          sSearch: "Buscar:",
          sProcessing: "Cargando data...",
          oPaginate: {
            sFirst: "Primero",
            sLast: "Último",
            sNext: "Siguiente",
            sPrevious: "Anterior"
          },
        },
        select: {
        style: 'multi'
        }
      });
    });
  </script>

</body>

</html>