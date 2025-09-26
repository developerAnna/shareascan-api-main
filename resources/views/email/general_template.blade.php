<!DOCTYPE html>
<html>

<head>
    <title>{{ $content->subject }}</title>
    <style type="text/css">
        .g-container {
            padding: 15px 30px;
        }
    </style>
</head>

<body>
    <div class="g-container">
        {{-- <h1>{!! xss_clean($content->body) !!}</h1> --}}
        <h1>{!! $content->body !!}</h1>
    </div>
</body>

</html>
