
<form action="{{ route('setLocale', $lang) }}" method="POST" class="d-inline">
    @csrf
    <button type="submit" class="btn p-0 border-0 bg-transparent nav-link display-inline">
        <img src="{{ asset('vendor/blade-flags/country-' . $lang . '.svg') }}" width="32" height="32"
            alt="Lingua {{ $lang }}" class="rounded shadow-sm flag-hover">
    </button>
</form>


