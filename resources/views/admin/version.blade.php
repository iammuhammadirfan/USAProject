<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Version Notes</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        h1 { margin-bottom: 20px; }
        .note { margin-bottom: 30px; border-bottom: 1px solid #ccc; padding-bottom: 10px; }
        .date { font-weight: bold; }
        .version { color: #007BFF; font-size: 1.2em; }
    </style>
</head>
<body>
    <h1>Version Notes</h1>
    @foreach ($notes as $note)
        <div class="note">
            <div class="date">{{ \Carbon\Carbon::parse($note->date)->format('F j, Y') }}</div>
            <div class="version">Version {{ $note->version }}</div>
            <div class="details">{!! nl2br(e($note->details)) !!}</div>
        </div>
    @endforeach
</body>
</html>
