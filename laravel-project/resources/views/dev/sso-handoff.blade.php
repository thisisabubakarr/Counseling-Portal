<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Redirecting from CMS…</title>
</head>
<body>
    <p>Redirecting from CMS…</p>
    <form id="ssoHandoff" method="POST" action="{{ route('sso.callback') }}">
        <input type="hidden" name="token" value="{{ $token }}">
    </form>
    <script>document.getElementById('ssoHandoff').submit();</script>
</body>
</html>
