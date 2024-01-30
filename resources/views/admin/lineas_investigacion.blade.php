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
        <div role="status" class="relative overflow-x-auto shadow-md sm:rounded-lg animate-pulse">
          <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
              <tr>
                <th scope="col" class="px-6 py-3">
                  <div class="h-2.5 bg-gray-200 rounded-full dark:bg-gray-700 w-48 mb-4"></div>
                </th>
                <th scope="col" class="px-6 py-3">
                  <div class="h-2.5 bg-gray-200 rounded-full dark:bg-gray-700 w-48 mb-4"></div>
                </th>
                <th scope="col" class="px-6 py-3">
                  <div class="h-2.5 bg-gray-200 rounded-full dark:bg-gray-700 w-48 mb-4"></div>
                </th>
                <th scope="col" class="px-6 py-3">
                  <div class="h-2.5 bg-gray-200 rounded-full dark:bg-gray-700 w-48 mb-4"></div>
                </th>
              </tr>
            </thead>
            <tbody>
              <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                  <div class="h-2.5 bg-gray-200 rounded-full dark:bg-gray-700 w-48 mb-4"></div>
                </th>
                <td class="px-6 py-4">
                  <div class="h-2.5 bg-gray-200 rounded-full dark:bg-gray-700 w-48 mb-4"></div>
                </td>
                <td class="px-6 py-4">
                  <div class="h-2.5 bg-gray-200 rounded-full dark:bg-gray-700 w-48 mb-4"></div>
                </td>
                <td class="px-6 py-4">
                  <div class="h-2.5 bg-gray-200 rounded-full dark:bg-gray-700 w-48 mb-4"></div>
                </td>
              </tr>
              <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                  <div class="h-2.5 bg-gray-200 rounded-full dark:bg-gray-700 w-48 mb-4"></div>
                </th>
                <td class="px-6 py-4">
                  <div class="h-2.5 bg-gray-200 rounded-full dark:bg-gray-700 w-48 mb-4"></div>
                </td>
                <td class="px-6 py-4">
                  <div class="h-2.5 bg-gray-200 rounded-full dark:bg-gray-700 w-48 mb-4"></div>
                </td>
                <td class="px-6 py-4">
                  <div class="h-2.5 bg-gray-200 rounded-full dark:bg-gray-700 w-48 mb-4"></div>
                </td>
              </tr>
              <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                  <div class="h-2.5 bg-gray-200 rounded-full dark:bg-gray-700 w-48 mb-4"></div>
                </th>
                <td class="px-6 py-4">
                  <div class="h-2.5 bg-gray-200 rounded-full dark:bg-gray-700 w-48 mb-4"></div>
                </td>
                <td class="px-6 py-4">
                  <div class="h-2.5 bg-gray-200 rounded-full dark:bg-gray-700 w-48 mb-4"></div>
                </td>
                <td class="px-6 py-4">
                  <div class="h-2.5 bg-gray-200 rounded-full dark:bg-gray-700 w-48 mb-4"></div>
                </td>
              </tr>
              <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                  <div class="h-2.5 bg-gray-200 rounded-full dark:bg-gray-700 w-48 mb-4"></div>
                </th>
                <td class="px-6 py-4">
                  <div class="h-2.5 bg-gray-200 rounded-full dark:bg-gray-700 w-48 mb-4"></div>
                </td>
                <td class="px-6 py-4">
                  <div class="h-2.5 bg-gray-200 rounded-full dark:bg-gray-700 w-48 mb-4"></div>
                </td>
                <td class="px-6 py-4">
                  <div class="h-2.5 bg-gray-200 rounded-full dark:bg-gray-700 w-48 mb-4"></div>
                </td>
              </tr>
              <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                  <div class="h-2.5 bg-gray-200 rounded-full dark:bg-gray-700 w-48 mb-4"></div>
                </th>
                <td class="px-6 py-4">
                  <div class="h-2.5 bg-gray-200 rounded-full dark:bg-gray-700 w-48 mb-4"></div>
                </td>
                <td class="px-6 py-4">
                  <div class="h-2.5 bg-gray-200 rounded-full dark:bg-gray-700 w-48 mb-4"></div>
                </td>
                <td class="px-6 py-4">
                  <div class="h-2.5 bg-gray-200 rounded-full dark:bg-gray-700 w-48 mb-4"></div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
      <div class="hidden p-4 rounded-lg bg-gray-50 dark:bg-gray-800" id="tab_crear" role="tabpanel" aria-labelledby="dashboard-tab">
        <!--  Crear nueva linea de investigación  -->
        <form class="max-w-md mx-auto">
          <div class="mb-5">
            <label for="facultad" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Facultad:</label>
            <select id="facultad" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
              <option>Ninguna</option>
              <option>Medicina</option>
              <option>France</option>
              <option>Germany</option>
            </select>
          </div>
          <div class="mb-5">
            <label for="padre" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Padre:</label>
            <select id="padre" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
              <option>Ninguno</option>
              <option>Medicina</option>
              <option>France</option>
              <option>Germany</option>
            </select>
          </div>
          <div class="mb-5">
            <label for="codigo" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Código:</label>
            <input type="text" id="codigo" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
          </div>
          <div class="mb-5">
            <label for="linea" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Linea:</label>
            <input type="text" id="linea" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
          </div>
          <button type="submit" class="text-white w-full bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">Guardar</button>
        </form>
      </div>
    </div>
  </div>
</body>

</html>