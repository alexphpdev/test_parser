<!DOCTYPE html>
<html>
<head>
    <title>test</title>
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.11.2/css/all.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700&display=swap">
    <link rel="stylesheet" href="{{ asset('css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/mdb.min.css') }}">
    <!-- Plugin file -->
    <link rel="stylesheet" href="{{ asset('css/addons/datatables.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body>
<div class="container">
    <div class="row">
        <table id="dtBasicExample" class="table table-striped table-bordered table-sm col" cellspacing="0" width="100%">
            <thead>
            <tr>
                <th class="th-sm">Дата</th>
                <th class="th-sm">Название</th>
                <th class="th-sm">Автор</th>
                <th class="th-sm">Теги</th>
            </tr>
            </thead>
        <tbody>
            @foreach($elements as $el)
                <tr>
                    <td>{{$el->dateFormated}}</td>
                    <td><a target="blank" href="{{$el->link}}">{{$el->title}}</a></td>
                    <td>{{$el->author}}</td>
                    <td>{{$el->tags}}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th class="th-sm">Дата</th>
                <th class="th-sm">Название</th>
                <th class="th-sm">Автор</th>
                <th class="th-sm">Теги</th>
            </tr>
        </tfoot>
        </table>
    </div>
</div>

    <script type="text/javascript" src="{{ asset('js/jquery.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/popper.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/bootstrap.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/mdb.min.js') }}"></script>
    <!-- Plugin file -->
    <script src="{{ asset('js/addons/datatables.min.js') }}"></script>

    <script>
        $(document).ready(function () {
            $('#dtBasicExample').DataTable({
                "order": [[ 2, "asc" ]]
            });
            $('.dataTables_length').addClass('bs-select');
        });
    </script>
</body>
</html>