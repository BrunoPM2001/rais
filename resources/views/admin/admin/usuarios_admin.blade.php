<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <meta name="csrf_token" content="{{ csrf_token() }}" />
  <title>Usuarios administrativos</title>
  @vite(['resources/scss/app.scss', 'resources/js/app.js'])
</head>

<body>
  @include('admin.components.navbar')
  <div class="container">
    <h4 class="my-4">Usuarios administrativos</h4>
    <!--  Tab list  -->
    <ul class="nav nav-tabs" id="myTab" role="tablist">
      <li class="nav-item" role="presentation">
        <button class="nav-link active" id="listar-tab" data-bs-toggle="tab" data-bs-target="#listar-tab-pane" type="button" role="tab" aria-controls="listar-tab-pane" aria-selected="true">Listar usuarios administrativos</button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link" id="crear-tab" data-bs-toggle="tab" data-bs-target="#crear-tab-pane" type="button" role="tab" aria-controls="crear-tab-pane" aria-selected="false">Crear usuario administrativo</button>
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
                  Id
                </th>
                <th>
                  Nombre de usuario
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
                  Teléfono móvil
                </th>
                <th>
                  Cargo
                </th>
                <th>
                  Fecha de creación
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
                  Id
                </th>
                <th>
                  Nombre de usuario
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
                  Teléfono móvil
                </th>
                <th>
                  Cargo
                </th>
                <th>
                  Fecha de creación
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
          <input type="text" hidden value="Usuario_admin" name="tipo">
          <div class="row mb-4">
            <label for="username" class="col-sm-4 col-form-label">Nombre de usuario:</label>
            <div class="col-sm-8">
              <input type="text" id="username" name="username" class="form-control">
            </div>
          </div>
          <div class="row mb-4">
            <label for="create_facultad" class="col-sm-4 col-form-label">Facultad:</label>
            <div class="col-sm-8">
              <select id="create_facultad" name="facultad_id" class="form-select">
                <option value="" selected>Ninguna</option>
                @foreach($facultades as $facultad)
                <option value="{{ $facultad->id }}">{{ $facultad->nombre }}</option>
                @endforeach
              </select>
            </div>
          </div>
          <div class="row mb-4">
            <label for="codigo_trabajador" class="col-sm-4 col-form-label">Código de trabajador:</label>
            <div class="col-sm-8">
              <input type="text" id="codigo_trabajador" name="codigo_trabajador" class="form-control">
            </div>
          </div>
          <div class="row mb-4">
            <label for="apellido1" class="col-sm-4 col-form-label">Apellido paterno:</label>
            <div class="col-sm-8">
              <input type="text" id="apellido1" name="apellido1" class="form-control">
            </div>
          </div>
          <div class="row mb-4">
            <label for="apellido2" class="col-sm-4 col-form-label">Apellido materno:</label>
            <div class="col-sm-8">
              <input type="text" id="apellido2" name="apellido2" class="form-control">
            </div>
          </div>
          <div class="row mb-4">
            <label for="nombres" class="col-sm-4 col-form-label">Nombres:</label>
            <div class="col-sm-8">
              <input type="text" id="nombres" name="nombres" class="form-control">
            </div>
          </div>
          <div class="row mb-4">
            <label for="sexo" class="col-sm-4 col-form-label">Sexo:</label>
            <div class="col-sm-8">
              <select id="sexo" name="sexo" class="form-select">
                <option value="M" selected>Masculino</option>
                <option value="F">Femenino</option>
              </select>
            </div>
          </div>
          <div class="row mb-4">
            <label for="fecha_nacimiento" class="col-sm-4 col-form-label">Fecha de nacimiento:</label>
            <div class="col-sm-8">
              <input type="date" id="fecha_nacimiento" name="fecha_nacimiento" class="form-control">
            </div>
          </div>
          <div class="row mb-4">
            <label for="email_admin" class="col-sm-4 col-form-label">Correo:</label>
            <div class="col-sm-8">
              <input type="email" id="email_admin" name="email_admin" class="form-control">
            </div>
          </div>
          <div class="row mb-4">
            <label for="telefono_casa" class="col-sm-4 col-form-label">Teléfono de casa:</label>
            <div class="col-sm-8">
              <input type="text" id="telefono_casa" name="telefono_casa" class="form-control">
            </div>
          </div>
          <div class="row mb-4">
            <label for="telefono_trabajo" class="col-sm-4 col-form-label">Teléfono de trabajo:</label>
            <div class="col-sm-8">
              <input type="text" id="telefono_trabajo" name="telefono_trabajo" class="form-control">
            </div>
          </div>
          <div class="row mb-4">
            <label for="telefono_movil" class="col-sm-4 col-form-label">Teléfono móvil:</label>
            <div class="col-sm-8">
              <input type="text" id="telefono_movil" name="telefono_movil" class="form-control">
            </div>
          </div>
          <div class="row mb-4">
            <label for="direccion1" class="col-sm-4 col-form-label">Dirección:</label>
            <div class="col-sm-8">
              <input type="text" id="direccion1" name="direccion1" class="form-control">
            </div>
          </div>
          <div class="row mb-4">
            <label for="cargo" class="col-sm-4 col-form-label">Cargo:</label>
            <div class="col-sm-8">
              <input type="text" id="cargo" name="cargo" class="form-control">
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
    Editar usuario administrador
    @endsection

    @section('contenido')
    <input type="text" hidden id="edit_id" name="edit_id">
    <input type="text" hidden id="tabla_id" name="tabla_id">
    <div class="row align-items-center mb-3">
      <label for="edit_username" class="col-sm-4 col-form-label">Nombre de usuario:</label>
      <div class="col-sm-8">
        <input type="text" id="edit_username" name="edit_username" class="form-control">
      </div>
    </div>
    <div class="row align-items-center mb-3">
      <label for="edit_facultad" class="col-sm-4 col-form-label">Facultad:</label>
      <div class="col-sm-8">
        <select id="edit_facultad" name="edit_facultad" class="form-select">
          <option value="" selected>Ninguna</option>
          @foreach($facultades as $facultad)
          <option value="{{ $facultad->id }}">{{ $facultad->nombre }}</option>
          @endforeach
        </select>
      </div>
    </div>
    <div class="row align-items-center mb-3">
      <label for="edit_codigo_trabajador" class="col-sm-4 col-form-label">Código de trabajador:</label>
      <div class="col-sm-8">
        <input type="text" id="edit_codigo_trabajador" name="edit_codigo_trabajador" class="form-control">
      </div>
    </div>
    <div class="row align-items-center mb-3">
      <label for="edit_apellido1" class="col-sm-4 col-form-label">Apellido paterno:</label>
      <div class="col-sm-8">
        <input type="text" id="edit_apellido1" name="edit_apellido1" class="form-control">
      </div>
    </div>
    <div class="row align-items-center mb-3">
      <label for="edit_apellido2" class="col-sm-4 col-form-label">Apellido materno:</label>
      <div class="col-sm-8">
        <input type="text" id="edit_apellido2" name="edit_apellido2" class="form-control">
      </div>
    </div>
    <div class="row align-items-center mb-3">
      <label for="edit_nombres" class="col-sm-4 col-form-label">Nombres:</label>
      <div class="col-sm-8">
        <input type="text" id="edit_nombres" name="edit_nombres" class="form-control">
      </div>
    </div>
    <div class="row align-items-center mb-3">
      <label for="edit_sexo" class="col-sm-4 col-form-label">Sexo:</label>
      <div class="col-sm-8">
        <select id="edit_sexo" name="edit_sexo" class="form-select">
          <option value="M">Masculino</option>
          <option value="F">Femenino</option>
        </select>
      </div>
    </div>
    <div class="row align-items-center mb-3">
      <label for="edit_fecha_nacimiento" class="col-sm-4 col-form-label">Fecha de nacimiento:</label>
      <div class="col-sm-8">
        <input type="date" id="edit_fecha_nacimiento" name="edit_fecha_nacimiento" class="form-control">
      </div>
    </div>
    <div class="row align-items-center mb-3">
      <label for="edit_email_admin" class="col-sm-4 col-form-label">Correo:</label>
      <div class="col-sm-8">
        <input type="email" id="edit_email_admin" name="edit_email_admin" class="form-control">
      </div>
    </div>
    <div class="row align-items-center mb-3">
      <label for="edit_telefono_casa" class="col-sm-4 col-form-label">Teléfono de casa:</label>
      <div class="col-sm-8">
        <input type="text" id="edit_telefono_casa" name="edit_telefono_casa" class="form-control">
      </div>
    </div>
    <div class="row align-items-center mb-3">
      <label for="edit_telefono_trabajo" class="col-sm-4 col-form-label">Teléfono de trabajo:</label>
      <div class="col-sm-8">
        <input type="text" id="edit_telefono_trabajo" name="edit_telefono_trabajo" class="form-control">
      </div>
    </div>
    <div class="row align-items-center mb-3">
      <label for="edit_telefono_movil" class="col-sm-4 col-form-label">Teléfono móvil:</label>
      <div class="col-sm-8">
        <input type="text" id="edit_telefono_movil" name="edit_telefono_movil" class="form-control">
      </div>
    </div>
    <div class="row align-items-center mb-3">
      <label for="edit_direccion1" class="col-sm-4 col-form-label">Dirección:</label>
      <div class="col-sm-8">
        <input type="text" id="edit_direccion1" name="edit_direccion1" class="form-control">
      </div>
    </div>
    <div class="row align-items-center mb-3">
      <label for="edit_cargo" class="col-sm-4 col-form-label">Cargo:</label>
      <div class="col-sm-8">
        <input type="text" id="edit_cargo" name="edit_cargo" class="form-control">
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
      //  Iniciar modal y toast
      let modal = new bootstrap.Modal(document.getElementById('myModal'), {});
      let toast = new bootstrap.Toast(document.getElementById('myToast'));
      //  Datatable
      let ajax_url = 'http://localhost:8000/api/usuarios/getUsuariosAdmin'
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
            data: 'username'
          },
          {
            render: function(data, type, row) {
              return row.user_admin.apellido1;
            }
          },
          {
            render: function(data, type, row) {
              return row.user_admin.apellido2;
            }
          },
          {
            render: function(data, type, row) {
              return row.user_admin.nombres;
            }
          },
          {
            render: function(data, type, row) {
              return row.user_admin.telefono_movil;
            }
          },
          {
            render: function(data, type, row) {
              return row.user_admin.cargo;
            }
          },
          {
            render: function(data, type, row) {
              let date = new Date(row.user_admin.created_at)
              return date.toLocaleDateString('es-ES', {
                day: "2-digit",
                month: "2-digit",
                year: "numeric"
              });
            }
          },
          {
            render: function(data, type, row) {
              return row.estado == 1 ? "Activo" : "Inactivo";
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
          url: 'http://localhost:8000/api/usuarios/getOneAdmin/' + id,
          type: 'GET',
          success: (data) => {
            //  Actualizar la data a editar
            $("#edit_id").val(data.id)
            $("#tabla_id").val(data.tabla_id)
            $("#edit_username").val(data.username);
            $("#edit_facultad").val(data.user_admin.facultad_id);
            $("#edit_codigo_trabajador").val(data.user_admin.codigo_trabajador);
            $("#edit_apellido1").val(data.user_admin.apellido1);
            $("#edit_apellido2").val(data.user_admin.apellido2);
            $("#edit_nombres").val(data.user_admin.nombres);
            $("#edit_sexo").val(data.user_admin.sexo);
            $("#edit_fecha_nacimiento").val(data.user_admin.fecha_nacimiento);
            $("#edit_email_admin").val(data.user_admin.email_admin);
            $("#edit_telefono_casa").val(data.user_admin.telefono_casa);
            $("#edit_telefono_trabajo").val(data.user_admin.telefono_trabajo);
            $("#edit_telefono_movil").val(data.user_admin.telefono_movil);
            $("#edit_direccion1").val(data.user_admin.direccion1);
            $("#edit_cargo").val(data.user_admin.cargo);
            modal.show();
          }
        });
      });
      //  Actualizar data
      $('#updateForm').on('submit', (e) => {
        e.preventDefault();
        $.ajax({
          url: 'http://localhost:8000/api/usuarios/update',
          type: 'POST',
          data: {
            id: $('#edit_id').val(),
            tipo: "Usuario_admin",
            tabla_id: $('#tabla_id').val(),
            username: $('#edit_username').val(),
            facultad_id: $('#edit_facultad').val(),
            codigo_trabajador: $('#edit_codigo_trabajador').val(),
            apellido1: $('#edit_apellido1').val(),
            apellido2: $('#edit_apellido2').val(),
            nombres: $('#edit_nombres').val(),
            sexo: $('#edit_sexo').val(),
            fecha_nacimiento: $('#edit_fecha_nacimiento').val(),
            email_admin: $('#edit_email_admin').val(),
            telefono_casa: $('#edit_telefono_casa').val(),
            telefono_trabajo: $('#edit_telefono_trabajo').val(),
            telefono_movil: $('#edit_telefono_movil').val(),
            direccion1: $('#edit_direccion1').val(),
            cargo: $('#edit_cargo').val(),
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
    });
  </script>
</body>

</html>