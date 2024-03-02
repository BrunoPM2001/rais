<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <meta name="csrf_token" content="{{ csrf_token() }}" />
  <title>Convocatorias pasadas</title>
  @vite(['resources/scss/app.scss', 'resources/js/app.js'])
</head>

<body>
  @include('admin.components.navbar')
  <div class="container mb-4">
    <h4 class="my-4">Convocatorias pasadas</h4>
    <!--  Tab list  -->
    <ul class="nav nav-tabs" id="myTab" role="tablist">
      <li class="nav-item" role="presentation">
        <button class="nav-link active" id="listar-tab" data-bs-toggle="tab" data-bs-target="#listar-tab-pane" type="button" role="tab" aria-controls="listar-tab-pane" aria-selected="true">Listar convocatorias</button>
      </li>
      <li class="nav-item" role="presentation">
        <button hidden class="nav-link" id="detalle-tab" data-bs-toggle="tab" data-bs-target="#detalle-tab-pane" type="button" role="tab" aria-controls="listar-tab-pane" aria-selected="true">Detalle de convocatoria</button>
      </li>
    </ul>
    <div class="tab-content border border-top-0 rounded-bottom" id="myTabContent">
      <div class="tab-pane fade show active p-4" id="listar-tab-pane" role="tabpanel" aria-labelledby="listar-tab" tabindex="0">
        <!--  Tabla  -->
        <div class="overflow-x-hidden">
          <table id="table" class="table table-striped table-hover align-middle" style="width:100%">
            <thead>
              <tr>
                <th>
                  Tipo
                </th>
                <th>
                  Periodo
                </th>
                <th>
                  Facultades
                </th>
                <th>
                  Cupos
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
                  Ver detalle
                </th>
              </tr>
            </thead>
            <tbody>
            </tbody>
            <tfoot>
              <tr>
                <th>
                  Tipo
                </th>
                <th>
                  Periodo
                </th>
                <th>
                  Facultades
                </th>
                <th>
                  Cupos
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
                  Ver detalle
                </th>
              </tr>
            </tfoot>
          </table>
        </div>
      </div>
      <div class="tab-pane fade p-4" id="detalle-tab-pane" role="tabpanel" aria-labelledby="detalle-tab" tabindex="0">
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
      //  Datatable
      let ajax_url = 'http://localhost:8000/api/admin/facultad/getConvocatorias'
      let table = new DataTable('#table', {
        paging: true,
        pagingType: 'full_numbers',
        deferRender: true,
        processing: true,
        lengthChange: false,
        scrollX: true,
        ajax: ajax_url,
        columns: [{
            data: 'tipo_proyecto'
          },
          {
            data: 'periodo'
          },
          {
            data: 'facultades'
          },
          {
            data: 'cupos'
          },
          {
            data: 'fecha_inicio'
          },
          {
            data: 'fecha_fin'
          },
          {
            data: 'fecha_inicio_evaluacion'
          },
          {
            data: 'fecha_fin_evaluacion'
          },
          {
            render: function(data, type, row) {
              return `<button id="s_${row.tipo_proyecto}_${row.periodo}" class="btn btn-warning createTab">Detalles</button>`;
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
      //  Iniciar nueva tab
      $('#table').on('click', '.createTab', (e) => {
        let item = e.currentTarget.getAttribute('id');
        let [_, tipo_proyecto, periodo] = item.split('_');
        $('#val_tipo_proyecto').val(tipo_proyecto);
        $('#val_periodo').val(periodo);
        //  Mostrar tab
        $('#detalle-tab').removeAttr('hidden');
        tab.show()
        //  Si no se ha inicializado
        if (!$.fn.DataTable.isDataTable('#table_detalles')) {
          //  Nueva tabla
          table2 = new DataTable('#table_detalles', {
            paging: true,
            // select: true,
            pagingType: 'full_numbers',
            deferRender: true,
            processing: true,
            lengthChange: false,
            scrollX: true,
            ajax: 'http://localhost:8000/api/admin/facultad/getDetalleConvocatoria/' + periodo + '/' + tipo_proyecto,
            columns: [{
                data: 'facultad'
              },
              {
                data: 'cupos'
              },
              {
                data: 'puntaje_minimo'
              },
              {
                data: 'fecha_inicio'
              },
              {
                data: 'fecha_fin'
              },
              {
                data: 'evaluacion_fecha_inicio'
              },
              {
                data: 'evaluacion_fecha_fin'
              },
              {
                data: 'evaluadores'
              }
            ],
            //  Idioma de la información mostrada
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
        } else {
          let ajax_url = 'http://localhost:8000/api/admin/facultad/getDetalleConvocatoria/' + periodo + '/' + tipo_proyecto;
          table2.clear().draw();
          table3.clear().draw();
          table2.ajax.url(ajax_url).load();
        }
        table2.on('click', 'tbody tr', (e) => {
          let classList = e.currentTarget.classList;
          if (classList.contains('selected')) {
            classList.remove('selected');
          } else {
            table2.rows('.selected').nodes().each((row) => row.classList.remove('selected'));
            classList.add('selected');
          }
        });
        table2.on('click', 'tbody tr', function() {
          let data = table2.row(this).data();
          //  Animación
          $('html, body').animate({
            scrollTop: $('#table_evaluadores').offset().top
          }, 1000);
          if (!$.fn.DataTable.isDataTable('#table_evaluadores')) {
            //  Nueva tabla
            table3 = new DataTable('#table_evaluadores', {
              paging: false,
              searching: false,
              deferRender: true,
              processing: true,
              lengthChange: false,
              scrollX: true,
              ajax: 'http://localhost:8000/api/admin/facultad/getEvaluadoresConvocatoria/' + data.id,
              columns: [{
                  data: 'tipo'
                },
                {
                  data: 'apellidos'
                },
                {
                  data: 'nombres'
                },
                {
                  data: 'institucion'
                },
                {
                  data: 'cargo'
                },
                {
                  data: 'codigo_regina'
                },
              ],
              //  Idioma de la información mostrada
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
          } else {
            let ajax_url = 'http://localhost:8000/api/admin/facultad/getEvaluadoresConvocatoria/' + data.id;
            table3.clear().draw();
            table3.ajax.url(ajax_url).load();
          }
        });
      });
    });
  </script>
</body>

</html>