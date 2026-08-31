<!DOCTYPE html>

<html>

<head>
    <title>Note Created</title>
</head>

<body>


<h2>Hello {{ $data['name'] }},</h2>

<p>
    Your note has been successfully created.
</p>

<p>
    <strong>Note:</strong>
    {{ $data['title'] }}
</p>

<p>
    <strong>Content:</strong>
    {{ $data['content'] }}
</p>

<p>
    Thank you for using Notes App.
</p>


</body>

</html>
