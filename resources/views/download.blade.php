<!DOCTYPE html>
<html>
<head>
    <title>Download Excel3</title>
    <style>
        body { font-family: Arial; text-align: center; padding-top: 100px; }
        a.button {
            display: inline-block;
            padding: 10px 20px;
            background: green;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <h2>Comparison Complete!</h2>
    <p>Your file is ready to download:</p>
    <a class="button" href="{{ route('download') }}">Download Excel3</a>
</body>
</html>