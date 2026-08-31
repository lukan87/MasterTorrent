<form method="POST" action="{{ route('security.unlock.submit') }}">
    @csrf
    <label>Parola de securitate</label>
    <input type="password" name="code" required autofocus>
    <button type="submit">Continua</button>
</form>
