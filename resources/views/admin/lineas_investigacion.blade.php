<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Lineas de investigación</title>
  @vite(['resources/css/app.css','resources/js/app.js'])
</head>

<body>
  @include('admin.components.navbar')
  <hr>
  <div class="mx-auto max-w-screen-xl p-4">
    <!--  Tabs  -->
    <div class="mb-4 border-b border-gray-200 dark:border-gray-700">
      <ul class="flex flex-wrap -mb-px text-sm font-medium text-center" id="default-tab" data-tabs-toggle="#default-tab-content" role="tablist">
        <li class="me-2" role="presentation">
          <button class="inline-block p-4 border-b-2 rounded-t-lg" id="listado-tab" data-tabs-target="#tab_listado" type="button" role="tab" aria-controls="tab_listado" aria-selected="false">Lista de lineas</button>
        </li>
        <li class="me-2" role="presentation">
          <button class="inline-block p-4 border-b-2 rounded-t-lg hover:text-gray-600 hover:border-gray-300 dark:hover:text-gray-300" id="crear-tab" data-tabs-target="#tab_crear" type="button" role="tab" aria-controls="tab_crear" aria-selected="false">Crear linea nueva</button>
        </li>
      </ul>
    </div>
    <div id="default-tab-content">
      <div class="hidden p-4 rounded-lg bg-gray-50 dark:bg-gray-800" id="tab_listado" role="tabpanel" aria-labelledby="profile-tab">
        <!--  Seleccionar facultad  -->
        <form class="max-w-sm mx-auto">
          <div class="mb-5">
            <label for="facultad" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Facultad:</label>
            <select id="facultad" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
              <option value="null" selected>Ninguna</option>
              @foreach($facultades as $facultad)
              <option value="{{ $facultad->id }}">{{ $facultad->nombre }}</option>
              @endforeach
            </select>
          </div>
        </form>
        <!--  Tabla de lineas de investigación  -->
        <div class="relative p-4 overflow-x-auto shadow-md sm:rounded-lg">
          <table id="lineas_table" class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
            <thead class="text-xs text-gray-700 uppercase dark:text-gray-400">
              <tr>
                <th scope="col" class="px-6 py-3 bg-gray-50 dark:bg-gray-800">
                  Código
                </th>
                <th scope="col" class="px-6 py-3">
                  Linea de investigación
                </th>
                <th scope="col" class="px-6 py-3 bg-gray-50 dark:bg-gray-800">
                  Resolución
                </th>
                <th scope="col" class="px-6 py-3">
                  Expandir
                </th>
              </tr>
            </thead>
            <tbody>
              @foreach($lineas as $linea)
              <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white bg-gray-50 dark:bg-gray-800">
                  {{ $linea->codigo }}
                </th>
                <td class="px-6 py-4">
                  {{ $linea->nombre }}
                </td>
                <td class="px-6 py-4 bg-gray-50 dark:bg-gray-800">
                  {{ $linea->resolucion }}
                </td>
                <td class="px-6 py-4 text-center">
                  Expandir
                </td>
              </tr>
              @endforeach
            </tbody>
          </table>
          <nav class="flex justify-between items-center">
            <div id="cant_elements">
              Mostrando 1 de 1 elementos
            </div>
            <ul id="pagination" class="inline-flex -space-x-px text-base h-10 my-4">
              <li>
                <a href="#" class="flex items-center justify-center px-4 h-10 ms-0 leading-tight text-gray-500 bg-white border border-e-0 border-gray-300 rounded-s-lg hover:bg-gray-100 hover:text-gray-700 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white">Anterior</a>
              </li>
              <li>
                <a href="#" aria-current="page" class="flex items-center justify-center px-4 h-10 text-blue-600 border border-gray-300 bg-blue-50 hover:bg-blue-100 hover:text-blue-700 dark:border-gray-700 dark:bg-gray-700 dark:text-white">1</a>
              </li>
              <li>
                <a href="#" class="flex items-center justify-center px-4 h-10 leading-tight text-gray-500 bg-white border border-gray-300 hover:bg-gray-100 hover:text-gray-700 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white">2</a>
              </li>
              <li>
                <a href="#" class="flex items-center justify-center px-4 h-10 leading-tight text-gray-500 bg-white border border-gray-300 rounded-e-lg hover:bg-gray-100 hover:text-gray-700 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white">Siguiente</a>
              </li>
            </ul>
          </nav>
        </div>
      </div>
      <div class="hidden p-4 rounded-lg bg-gray-50 dark:bg-gray-800" id="tab_crear" role="tabpanel" aria-labelledby="dashboard-tab">
        <!--  Crear nueva linea de investigación  -->
        <form action="{{ route('create_linea') }}" method="post" class="max-w-md mx-auto">
          @csrf
          <div class="mb-5">
            <label for="create_facultad" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Facultad:</label>
            <select id="create_facultad" name="facultad_id" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
              <option value="">Ninguna</option>
              @foreach($facultades as $facultad)
              <option value="{{ $facultad->id }}">{{ $facultad->nombre }}</option>
              @endforeach
            </select>
          </div>
          <div class="mb-5">
            <label for="create_padre" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Padre:</label>
            <select id="create_padre" name="parent_id" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
              <option value="">Cargando...</option>
            </select>
          </div>
          <div class="mb-5">
            <label for="codigo" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Código:</label>
            <input type="text" id="codigo" name="codigo" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
          </div>
          <div class="mb-5">
            <label for="linea" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Linea:</label>
            <input type="text" id="nombre" name="nombre" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
          </div>
          <div class="mb-5">
            <label for="resolucion" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Resolución:</label>
            <input type="text" id="resolucion" name="resolucion" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
          </div>
          <button type="submit" class="text-white w-full bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">Guardar</button>
        </form>
      </div>
    </div>
  </div>
  <script type="module">
    $(document).ready(function() {
      //  Vars
      let state = false;
      let actualPage = 1;
      let lastPage = 1;
      //  Formulario de creación de línea
      $("#tab_crear").on('click', function() {
        if (!state) {
          $.ajax({
            url: "http://localhost:8000/ajaxGetLineasInvestigacion/",
            method: "GET",
            success: (data) => {
              let select = $("#create_padre");
              select.html("");
              select.append(
                $("<option>", {
                  value: "",
                  text: "Ninguno",
                  selected: true,
                })
              );
              $.each(data, (index, item) => {
                let option = $("<option>", {
                  value: item.id,
                  text: item.codigo + " " + item.nombre,
                });
                select.append(option);
              });
            },
          });
          state = true;
        }
      });

      //  Actualizar tabla
      $('#facultad').change(() => {
        updateData(1)
      });

      const changePage = () => {
        $('#pagination li a').click((e) => {
          let goTo;
          e.preventDefault();
          if (e.target.text == 'Anterior') {
            goTo = actualPage - 1;
          } else if (e.target.text == 'Siguiente') {
            goTo = actualPage + 1;
          } else {
            goTo = e.target.text;
          }
          updateData(goTo);
        });
      }
      changePage();

      const updateData = (page) => {
        $.ajax({
          url: "http://localhost:8000/ajaxGetLineasInvestigacionFacultadPag/" + $("#facultad").val() + "/" + page,
          method: "GET",
          success: (data) => {
            //  Paginación
            let ul = $('#pagination');
            ul.empty();
            ul.append($('<li>').html('<a href="#" aria-current="page" class="flex items-center justify-center px-4 h-10 text-blue-600 rounded-s-lg border border-gray-300 bg-blue-50 hover:bg-blue-100 hover:text-blue-700 dark:border-gray-700 dark:bg-gray-700 dark:text-white">Anterior</a>'));
            for (let i = 1; i <= data.last_page; i++) {
              let listItem = $('<li>');
              if (i == data.current_page) {
                listItem.html('<a href="#" aria-current="page" class="flex items-center justify-center px-4 h-10 text-blue-600 border border-gray-300 bg-blue-50 hover:bg-blue-100 hover:text-blue-700 dark:border-gray-700 dark:bg-gray-700 dark:text-white">' + i + '</a>')
              } else {
                listItem.html('<a href="#" class="flex items-center justify-center px-4 h-10 leading-tight text-gray-500 bg-white border border-gray-300 hover:bg-gray-100 hover:text-gray-700 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white">' + i + '</a>')
              }
              ul.append(listItem);
            }
            ul.append($('<li>').html('<a href="#" aria-current="page" class="flex items-center justify-center px-4 h-10 text-blue-600 rounded-e-lg border border-gray-300 bg-blue-50 hover:bg-blue-100 hover:text-blue-700 dark:border-gray-700 dark:bg-gray-700 dark:text-white">Siguiente</a>'));
            changePage();
            //  Tabla
            $('#lineas_table tbody').empty();
            $.each(data.data, function(index, linea) {
              let row = '<tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">' +
                '<th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white bg-gray-50 dark:bg-gray-800">' +
                linea.codigo +
                '</th>' +
                '<td class="px-6 py-4">' +
                linea.nombre +
                '</td>' +
                '<td class="px-6 py-4 bg-gray-50 dark:bg-gray-800">' +
                `${linea.resolucion == null ? "Ninguna" : linea.resolucion}` +
                '</td>' +
                '<td class="px-6 py-4 text-center text-blue-600 cursor-pointer">' +
                'Expandir' +
                '</td>' +
                '</tr>';
              // Agregar la fila a la tabla
              $('#lineas_table tbody').append(row);
            });
            //  Asignación final de valores
            actualPage = data.current_page;
            $('#cant_elements').text("Mostrando del " + data.from + " al " + data.to + " de un total de " + data.total + " elementos.")
          },
        });
      }
      updateData(1)
    })
  </script>
</body>

</html>