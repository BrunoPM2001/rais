<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <meta name="csrf_token" content="{{ csrf_token() }}" />
  <title>Usuarios investigadores</title>
  @vite(['resources/scss/app.scss', 'resources/js/app.js'])
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tarekraafat/autocomplete.js@10.2.7/dist/css/autoComplete.02.min.css">
</head>

<body>
  @include('admin.components.navbar')
  <div class="container mb-4">

    @if ($errors->any())
    <!--  Errores enviados por el controlador  -->
    <div class="alert alert-danger mt-4">
      <ul class="m-0">
        @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
    @endif

    <h4 class="my-4">Usuarios investigadores</h4>
    <!--  Tab list  -->
    <ul class="nav nav-tabs" id="myTab" role="tablist">
      <li class="nav-item" role="presentation">
        <button class="nav-link active" id="listar-tab" data-bs-toggle="tab" data-bs-target="#listar-tab-pane" type="button" role="tab" aria-controls="listar-tab-pane" aria-selected="true">Listar usuarios investigadores</button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link" id="crear-tab" data-bs-toggle="tab" data-bs-target="#crear-tab-pane" type="button" role="tab" aria-controls="crear-tab-pane" aria-selected="false">Crear usuario investigador</button>
      </li>
    </ul>
    <div class="tab-content border border-top-0 rounded-bottom mb-4" id="myTabContent">
      <div class="tab-pane fade show active p-4" id="listar-tab-pane" role="tabpanel" aria-labelledby="listar-tab" tabindex="0">
        <!--  Tabla  -->
        <div class="overflow-x-hidden">
          <table id="table" class="table table-striped table-hover align-middle" style="width:100%">
            <thead>
              <tr>
                <th>
                  Facultad
                </th>
                <th>
                  Codigo
                </th>
                <th>
                  Ap. Paterno
                </th>
                <th>
                  Ap. Materno
                </th>
                <th>
                  Nombres
                </th>
                <th>
                  Sexo
                </th>
                <th>
                  Email
                </th>
                <th>
                  Dni
                </th>
                <th>
                  Estado
                </th>
                <th>
                  Acción
                </th>
              </tr>
            </thead>
            <tbody>
            </tbody>
            <tfoot>
              <tr>
                <th>
                  Facultad
                </th>
                <th>
                  Codigo
                </th>
                <th>
                  Ap. Paterno
                </th>
                <th>
                  Ap. Materno
                </th>
                <th>
                  Nombres
                </th>
                <th>
                  Sexo
                </th>
                <th>
                  Email
                </th>
                <th>
                  Dni
                </th>
                <th>
                  Estado
                </th>
                <th>
                  Acción
                </th>
              </tr>
            </tfoot>
          </table>
        </div>
      </div>
      <div class="tab-pane fade" id="crear-tab-pane" role="tabpanel" aria-labelledby="crear-tab" tabindex="0">
        <!--  Crear nuev@  -->
        <form action="{{ route('create_usuario') }}" method="post" class="p-4">
          @csrf
          <input type="text" hidden value="Usuario_investigador" name="tipo">
          <div class="row mb-4">
            <label for="investigador_id" class="col-sm-4 col-form-label">Investigador:</label>
            <div class="col-sm-8">
              <input type="text" id="investigador_id" name="investigador_id" class="form-control">
              <input type="text" hidden id="id" name="id">
              <div class="dropdown-menu" id="autocomplete-dropdown" style="display:none">
                <!-- Aquí se mostrarán los resultados -->
              </div>
            </div>
          </div>
          <div class="row mb-4">
            <label for="email" class="col-sm-4 col-form-label">Email (para el acceso):</label>
            <div class="col-sm-8">
              <input type="text" id="email" name="email" class="form-control">
            </div>
          </div>
          <div class="row mb-4">
            <label for="password" class="col-sm-4 col-form-label">Contraseña:</label>
            <div class="col-sm-8">
              <input type="text" id="password" name="password" class="form-control">
            </div>
          </div>
          <div class="d-grid">
            <button type="submit" class="btn btn-primary">Guardar</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Modal para editar -->
  @extends('admin.components.modal')
  @section('form')
  <form id="updateForm">
    @endsection

    @section('titulo')
    Editar usuario investigador
    @endsection

    @section('contenido')
    <input type="text" hidden id="edit_id" name="edit_id">
    <div class="row align-items-center mb-3">
      <label for="edit_email" class="col-sm-4 col-form-label">Email de acceso:</label>
      <div class="col-sm-8">
        <input type="text" id="edit_email" name="edit_email" class="form-control">
      </div>
    </div>
    <div class="row align-items-center mb-3">
      <label for="edit_estado" class="col-sm-4 col-form-label">Estado:</label>
      <div class="col-sm-8">
        <select name="edit_estado" id="edit_estado" class="form-select">
          <option value="1">Activo</option>
          <option value="0">Inactivo</option>
        </select>
      </div>
    </div>
    <div class="row align-items-center mb-3">
      <label for="edit_password" class="col-sm-4 col-form-label">Contraseña:</label>
      <div class="col-sm-8">
        <input type="text" id="edit_password" name="edit_password" class="form-control">
      </div>
    </div>
    @endsection

    @section('end_form')
  </form>
  @endsection

  <!--  Toast para notificaciones -->
  @extends('admin.components.toast')

  <!--  TODO - AÑADIR TOAST CUANDO OCURRA UN ERROR AL CREAR UN USUARIO  -->

  <script type="module">
    $(document).ready(function() {
      //  Iniciar modal, toast, temporizador y autocompleteJS
      let modal = new bootstrap.Modal(document.getElementById('myModal'), {});
      let toast = new bootstrap.Toast(document.getElementById('myToast'));
      let temp;
      //  Datatable
      let ajax_url = 'http://localhost:8000/api/admin/usuarios/getUsuariosInvestigadores'
      let table = new DataTable('#table', {
        paging: true,
        pagingType: 'full_numbers',
        deferRender: true,
        processing: true,
        lengthChange: false,
        scrollX: true,
        ajax: ajax_url,
        columns: [{
            data: 'facultad'
          },
          {
            data: 'codigo'
          },
          {
            data: 'apellido1'
          },
          {
            data: 'apellido2'
          },
          {
            data: 'nombres'
          },
          {
            data: 'sexo'
          },
          {
            data: 'email'
          },
          {
            data: 'doc_numero'
          },
          {
            render: function(data, type, row) {
              return row.estado == 0 ? "Inactivo" : "Activo";
            }
          },
          {
            render: function(data, type, row) {
              return `<button id="s_${row.id}" class="btn btn-warning edit">Editar</button>`;
            }
          }
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
        }
      });
      //  Para el modal de update
      $('#table').on('click', '.edit', (e) => {
        let item = e.currentTarget.getAttribute('id');
        let [_, id] = item.split('_');
        $.ajax({
          url: 'http://localhost:8000/api/admin/usuarios/getOneInvestigador/' + id,
          type: 'GET',
          success: (data) => {
            //  Actualizar la data a editar
            $("#edit_id").val(data.id)
            $("#edit_estado").val(data.estado);
            $("#edit_email").val(data.email);
            modal.show();
          }
        });
      });
      //  Actualizar data
      $('#updateForm').on('submit', (e) => {
        e.preventDefault();
        $.ajax({
          url: 'http://localhost:8000/api/admin/usuarios/update',
          type: 'POST',
          data: {
            id: $('#edit_id').val(),
            tipo: "Usuario_investigador",
            email: $('#edit_email').val(),
            estado: $('#edit_estado').val(),
            password: $('#edit_password').val(),
          },
          headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf_token"]').attr('content')
          },
          success: (data) => {
            modal.hide();
            toast.show();
            $('#myToast .toast-body').html("Usuario actualizado con éxito.")
            $("#table").DataTable().ajax.reload();
            setTimeout(() => {
              toast.hide();
            }, 10000)
          }
        })
      })

      // Función para mostrar las sugerencias
      function showSuggestions(data) {
        var dropdownMenu = $('#autocomplete-dropdown');
        dropdownMenu.empty();

        data.forEach(function(suggestion) {
          var item = $('<a class="dropdown-item" href="#" myId="' + suggestion.id + '">').text(suggestion.content);
          dropdownMenu.append(item);
        });

        dropdownMenu.css('display', 'block');
      }

      // Evento keyup para el input
      $('#investigador_id').on('keyup', function() {
        let input = $('#investigador_id').val();
        clearTimeout(temp);
        temp = setTimeout(() => {
          //  Petición ajax y uso de typeahead
          $.ajax({
            url: 'http://localhost:8000/api/admin/usuarios/searchInvestigadorBy/' + input,
            method: 'GET',
            success: (data) => {
              let sugerencias = [];
              $.each(data, (index, user) => {
                sugerencias.push({
                  id: user.id,
                  content: user.codigo + " | " + user.doc_numero + " | " +
                    user.apellido1 + " " + user.apellido2 + " " + user.nombres
                });
              });
              showSuggestions(sugerencias)
            }
          });
        }, 1000);
        if (input.length == 0) {
          $('#autocomplete-dropdown').css('display', 'none');
        }
      });

      // Evento click para las sugerencias
      $('#autocomplete-dropdown').on('click', '.dropdown-item', function() {
        var id = $(this).attr("myId");
        var text = $(this).text();
        $('#investigador_id').val(text);
        $('#id').val(id);
        $('#autocomplete-dropdown').css('display', 'none');
      });

      // Evento blur para ocultar las sugerencias cuando se hace clic fuera del input
      $(document).on('click', function(event) {
        if (!$(event.target).closest('.input-group').length) {
          $('#autocomplete-dropdown').css('display', 'none');
        }
      });

    });
  </script>
</body>

</html>