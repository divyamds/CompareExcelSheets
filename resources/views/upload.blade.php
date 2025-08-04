<!DOCTYPE html>
<html>
<head>
    <title>Compare Excel Files</title>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; max-width: 600px; margin: auto; padding-top: 50px; }
        input, button { margin: 10px 0; display: block; }
        .error { color: red; }
    </style>
</head>
<body>
    <h1>Upload Excel1 & Excel2</h1>

    @if ($errors->any())
        <div class="error">
            <ul>@foreach($errors->all() as $e) <li>{{ $e }}</li> @endforeach</ul>
        </div>
    @endif


    <form method="POST" action="{{ route('compare') }}" enctype="multipart/form-data">
        @csrf  
        <label>Project Name:</label>
    <input type="text" name="project_name" required><br><br>

        <label>Excel 1:</label>
        <input type="file" name="excel1" accept=".xlsx,.xls" required>
        <label>Excel 2:</label>
        <input type="file" name="excel2" accept=".xlsx,.xls" required>
        <button type="submit">Compare & Generate</button>
    </form>
</body>
</html>