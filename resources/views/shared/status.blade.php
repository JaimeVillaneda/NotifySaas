@if (session('status'))
    <div class="mb-4 rounded border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
        {{ session('status') }}
    </div>
@endif

@if ($errors->any())
    <div class="mb-4 rounded border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
        Revisa los campos marcados.
    </div>
@endif
