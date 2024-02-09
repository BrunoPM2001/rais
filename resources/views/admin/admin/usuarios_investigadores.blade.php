<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <meta name="csrf_token" content="{{ csrf_token() }}" />
  <title>Usuarios investigadores</title>
  @vite(['resources/scss/app.scss', 'resources/js/app.js'])
</head>

<body>
  @include('admin.components.navbar')
  <div class="container mb-4">
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
            <label for="investigador_id" class="col-sm-4 col-form-label">Id:</label>
            <div class="col-sm-8">
              <input type="text" id="investigador_id" name="investigador_id" class="form-control">
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
      //  Iniciar modal, toast y temporizador
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
            data: 'estado'
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
            email: $('#edit_email').val(),
            tipo: "Usuario_investigador",
            estado: $('#edit_estado').val(),
            password: $('#edit_password').val()
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
      });
      //  Buscar investigador
      $('#investigador_id').on('keyup', () => {
        clearTimeout(temp);
        temp = setTimeout(() => {
          //  Petición ajax y uso de typeahead
          let input = $('#investigador_id').val();
          $.ajax({
            url: 'http://localhost:8000/api/admin/usuarios/searchInvestigadorBy/' + input,
            method: 'GET',
            success: (data) => {
              let sugerencias = [];
              $.each(data, (index, user) => {
                sugerencias.push(user.codigo + " | " + user.doc_numero + " | " +
                  user.apellido1 + " " + user.apellido2 + " " + user.nombre
                );
              });
              console.log(sugerencias)
              //  Iniciar Typeahead
              $('#investigador_id').typeahead({
                source: sugerencias
              });
            }
          });
        }, 1000);
      });
    });
  </script>
</body>

</html>