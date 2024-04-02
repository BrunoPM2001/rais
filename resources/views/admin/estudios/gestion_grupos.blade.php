<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <meta name="csrf_token" content="{{ csrf_token() }}" />
  <title>Rais web</title>
  @vite(['resources/scss/app.scss', 'resources/js/app.js'])

</head>

<body>
  @include('admin.components.navbar')
  <div class="text-bg-secondary">
    <p class="container-fluid"><strong>Grupos</strong></p>
  </div>
  <div class="container mb-4">  
    <!--  Tab list  -->
    <ul class="nav nav-tabs" id="myTab" role="tablist">
      <li class="nav-item" role="presentation">
        <button class="nav-link active" id="listar-tab" data-bs-toggle="tab" data-bs-target="#listar-tab-pane" type="button" role="tab" aria-controls="listar-tab-pane" aria-selected="true">Grupos</button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link" id="listar2-tab" data-bs-toggle="tab" data-bs-target="#listar2-tab-pane" type="button" role="tab" aria-controls="listar-tab-pane" aria-selected="true">Solicitudes</button>
      </li>
      <li class="nav-item" role="presentation">
        <button hidden class="nav-link" id="detalle-tab" data-bs-toggle="tab" data-bs-target="#detalle-tab-pane" type="button" role="tab" aria-controls="listar-tab-pane" aria-selected="true">Detalle</button>
      </li>
      <li class="nav-item" role="presentation" style="margin-left: 5px;">
        <div style="display: flex; justify-content: center; align-items: center; height: 100%;">
            <button class="btn btn-primary" id="BotonVisualizar" type="button" style="padding:3px;">Visualizar</button>
        </div>
      </li>
    </ul>
    <!--tabla gupos-->
    <div class="tab-content border border-top-0 rounded-bottom" id="myTabContent">
      <div class="tab-pane fade show active p-4" id="listar-tab-pane" role="tabpanel" aria-labelledby="listar-tab" tabindex="0">
        <!--  Tabla  -->
        <div class="overflow-x-hidden">
          <table id="table" class="table table-striped table-hover align-middle" style="width:100%">
            <thead>
              <tr>
                <th>
                  ID
                </th>
                <th>
                  Nombre grupo
                </th>
                <th>
                  Nombre corto
                </th>
                <th>
                  Categoría
                </th>
                <th>
                  Coordinador
                </th>
                <th>
                  Integrantes
                </th>
                <th>
                  Facultad
                </th>
                <th>
                  RR
                </th>
                <th>
                  Fecha de actualización
                </th>
                <th>
                  Fecha de creación
                </th>
                <th>
                  Estado
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
                  Nombre grupo
                </th>
                <th>
                  Nombre corto
                </th>
                <th>
                  Categoría
                </th>
                <th>
                  Coordinador
                </th>
                <th>
                  Integrantes
                </th>
                <th>
                  Facultad
                </th>
                <th>
                  RR
                </th>
                <th>
                  Fecha de actualización
                </th>
                <th>
                  Fecha de creación
                </th>
                <th>
                  Estado
                </th>
              </tr>
            </tfoot>
          </table>
        </div>
      </div>
      <!-- tabla 2-->
      <div class="tab-content border border-top-0 rounded-bottom" id="myTabContent">
      <div class="tab-pane fade hide active p-4" id="listar2-tab-pane" role="tabpanel" aria-labelledby="listar2-tab" tabindex="0">
        <!--  Tabla  -->
        <div class="overflow-x-hidden">
          <table id="table2" class="table table-striped table-hover align-middle" style="width:100%">
            <thead>
              <tr>
                <th>
                  ID
                </th>
                <th>
                  Nombre grupo
                </th>
                <th>
                  Nombre corto
                </th>
                <th>
                  Coordinador
                </th>
                <th>
                  Integrantes
                </th>
                <th>
                  Facultad
                </th>
                <th>
                  Fecha de actualización
                </th>
                <th>
                  Fecha de creación
                </th>
                <th>
                  Estado
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
                  Nombre grupo
                </th>
                <th>
                  Nombre corto
                </th>
                <th>
                  Coordinador
                </th>
                <th>
                  Integrantes
                </th>
                <th>
                  Facultad
                </th>
                <th>
                  Fecha de actualización
                </th>
                <th>
                  Fecha de creación
                </th>
                <th>
                  Estado
                </th>
              </tr>
            </tfoot>
          </table>
        </div>
      </div>

      <!--tabla 3-->
      <div class="tab-pane fade p-4" id="detalle-tab-pane" role="tabpanel" aria-labelledby="detalle-tab" tabindex="0">
          <!---->
          <ul class="nav nav-tabs" id="myTab" role="tablist">
            <li class="nav-item" role="presentation">
              <button class="nav-link active" id="nav_id" data-bs-toggle="tab" data-bs-target="#listar-tab-pane" type="button" role="tab" aria-controls="listar-tab-pane" aria-selected="true">ID</button>
            </li>
            <li class="nav-item" role="presentation">
              <button class="nav-link" id="nav_datos" data-bs-toggle="tab" data-bs-target="#listar2-tab-pane" type="button" role="tab" aria-controls="listar-tab-pane" aria-selected="true">Datos del grupo</button>
            </li>
            <li class="nav-item" role="presentation">
              <button class="nav-link" id="nav_editar" data-bs-toggle="tab" data-bs-target="#detalle-tab-pane" type="button" role="tab" aria-controls="listar-tab-pane" aria-selected="true">Editar</button>
            </li>
          </ul>

          <div class="container" style="padding: 15px; border:1px solid #e3e3e3; margin-top:15px; margin-bottom:15px; border-radius: 4px;">
            Aqui va el titulo
          </div>
          <div class="container row d-flex justify-content-between align-items">
            <div class="col-sm-3" style="padding: 15px; border:1px solid #e3e3e3; margin-top:15px; margin-bottom:15px; border-radius: 4px;">
              <label class="col-form-label" style="display: block; padding:0px; margin:0px;">RR creacion:<b> gaaaa</b></label>
              <label class="col-form-label" style="display: block; padding:0px; margin:0px;">RR fecha creacion:<b> gaaaa</b></label>
              <label class="col-form-label" style="display: block; padding:0px; margin:0px;">RR actual:<b> gaaaa</b></label>
              <label class="col-form-label" style="display: block; padding:0px; margin:0px;">RR fecha creacion:<b> gaaaa</b></label>
            </div>
            
            <div class="col-sm-8" style="padding: 15px; border:1px solid #e3e3e3; margin-top:15px; margin-bottom:15px; border-radius: 4px;">
              <label class="col-form-label" style="display: block; padding:0px; margin:0px;">
                <b>Observaciones:</b></label>
                <hr>
              <label class="col-form-label" style="display: block; padding:0px; margin:0px;">
                <b>Observaciones al investigador:</b></label>
            </div>
          </div>
          <!---->
        <div class="row align-items-center my-4">
          <div class="col-sm-3">
            <label class="col-form-label">Tipo de proyecto</label>
          </div>
          <div class="col-sm-3">
            <input type="text" disabled class="form-control" id="val_tipo_proyecto">
          </div>
          <div class="col-sm-3">
            <label class="col-form-label">Periodo</label>
          </div>
          <div class="col-sm-3">
            <input type="text" disabled class="form-control" id="val_periodo">
          </div>
        </div>
        <table id="table_detalles" class="table table-striped table-hover align-middle" style="width:100%">
          <thead>
            <tr>
                <th>
                  ID
                </th>
                <th>
                  Nombre grupo
                </th>
                <th>
                  Nombre corto
                </th>
                <th>
                  Coordinador
                </th>
                <th>
                  Integrantes
                </th>
                <th>
                  Facultad
                </th>
                <th>
                  Fecha de actualización
                </th>
                <th>
                  Fecha de creación
                </th>
                <th>
                  Estado
                </th>
            </tr>
          </thead>
          <tbody></tbody>
          <tfoot>
            <tr>
              <th>
                Facultad
              </th>
              <th>
                Cupos
              </th>
              <th>
                Puntaje mínimo
              </th>
              <th>
                Fecha de inicio
              </th>
              <th>
                Fecha de fin
              </th>
              <th>
                Inicio de evaluación
              </th>
              <th>
                Fin de evaluación
              </th>
              <th>
                Evaluadores
              </th>
            </tr>
          </tfoot>
        </table>
        <hr>
        <div>
          <h5 class="my-4">Evaluadores de convocatoria</h5>
          <table id="table_evaluadores" class="table table-striped table-hover align-middle" style="width:100%">
            <thead>
              <tr>
                <th>
                  Tipo
                </th>
                <th>
                  Apellidos
                </th>
                <th>
                  Nombres
                </th>
                <th>
                  Institución
                </th>
                <th>
                  Cargo
                </th>
                <th>
                  Código regina
                </th>
              </tr>
            </thead>
            <tbody></tbody>
            <tfoot>
              <tr>
                <th>
                  Tipo
                </th>
                <th>
                  Apellidos
                </th>
                <th>
                  Nombres
                </th>
                <th>
                  Institución
                </th>
                <th>
                  Cargo
                </th>
                <th>
                  Código regina
                </th>
              </tr>
            </tfoot>
          </table>
        </div>
      </div>
    </div>
  </div>

  <!--  Toast para notificaciones -->
  @extends('admin.components.toast')
  

  <script type="module">
    $(document).ready(function() {
      //  Iniciar tabla, toast y tab
      let table2, table3
      let toast = new bootstrap.Toast(document.getElementById('myToast'));
      let tab = new bootstrap.Tab(document.getElementById('detalle-tab'));


      function VisualizarGrupo() {
        /*Fila tabla1*/
        var table = $("#table").DataTable();
        var selectedRows = table.rows({ selected: true }).count();
        var selectedRow = table.row({selected: true });
        //var rowId = selectedRow.data().id;

        if (selectedRows===1) {
          var rowId = selectedRow.data().id;
          console.log(rowId)
          console.log("table")
          $('#detalle-tab').removeAttr('hidden');
          //$('#BotonVisualizar').hide();
          $('#detalle-tab').click();
          
        }else {
          alert("Por favor, seleccione un registro para visualizar.");
        }

      }
      $("#BotonVisualizar").on("click",VisualizarGrupo)



      //  Datatable grupos
      let ajax_url = 'http://localhost:8000/api/admin/estudios/listadoGrupos'
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
            data: 'grupo_nombre'
          },
          {
            data: 'grupo_nombre_corto'
          },
          {
            data: 'grupo_categoria'
          },
          {
            data: 'coordinador'
          },
          {
            data: 'cantidad_integrantes'
          },
          {
            data: 'facultad'
          },
          {
            data: 'resolucion_rectoral'
          },
          {
            data: 'updated_at'
          },
          {
            data: 'created_at'
          },
          {
            data: 'created_at'
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
        },
        select: {
          style: 'multi'
        }
      });


      //  Datatable solicitudes
      let ajax_url2 = 'http://localhost:8000/api/admin/estudios/listadoSolicitudes'
      let table_sol = new DataTable('#table2', {
        paging: true,
        pagingType: 'full_numbers',
        deferRender: true,
        processing: true,
        lengthChange: false,
        scrollX: true,
        ajax: ajax_url2,
        columns: [{
            data: 'id'
          },
          {
            data: 'grupo_nombre'
          },
          {
            data: 'grupo_nombre_corto'
          },
          {
            data: 'coordinador'
          },
          {
            data: 'cantidad_integrantes'
          },
          {
            data: 'facultad'
          },
          {
            data: 'updated_at'
          },
          {
            data: 'created_at'
          },
          {
            data: 'estado'
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
        },
        select: {
          style: 'multi'
        }
      });
    });
  </script>
</body>

</html>