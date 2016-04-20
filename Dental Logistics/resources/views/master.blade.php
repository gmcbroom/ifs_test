<!-- Stored in resources/views/layouts/master.blade.php -->

<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport"    content="width=device-width, initial-scale=1.0">
        <meta name="description" content="">
        <meta name="author"      content="G McBroom">

        <title>Demo Logistics</title>

        <link rel="shortcut icon" href="/images/gt_favicon.png">

        <link rel="stylesheet" media="screen" href="http://fonts.googleapis.com/css?family=Open+Sans:300,400,700">
        <link rel="stylesheet" href="//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css">
        <link rel="stylesheet" href="/css/bootstrap.min.css">
        <link rel="stylesheet" href="/css/font-awesome.min.css">

        <!-- Custom styles for our template -->
        <link rel="stylesheet" href="{{ elixir('css/app.css') }}">

        <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
        <!--[if lt IE 9]>
        <script src="/js/html5shiv.js"></script>
        <script src="/js/respond.min.js"></script>
        <!-- [endif]-->
    </head>
    <body>

        @include('navbar')

        @yield('content')

        <!--    include('footer1')  -->
        @include('footer1')

        <!--    include('footer2')  -->
        @include('footer2')

        <!-- JavaScript libs are placed at the end of the document so the pages load faster -->
        <script src="//code.jquery.com/jquery-1.10.2.js"></script>
        <script src="//code.jquery.com/ui/1.11.4/jquery-ui.js"></script>
        <script src="http://netdna.bootstrapcdn.com/bootstrap/3.0.0/js/bootstrap.min.js"></script>
        <script src="/js/headroom.min.js"></script>
        <script src="/js/jQuery.headroom.min.js"></script>
        <script src="/js/template.js"></script>
        <script>
            $(function () {
                $("#datepicker").datepicker({
                    dateFormat: "yy-mm-dd"
                });
            });
        </script>
    </script>
</body>

</html>
