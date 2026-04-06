@if ($errors->any())
    <div class="custom-error-box">
        <ul>
            @foreach ($errors->all() as $error)
                <li>.<span>{{ $error }}</span></li>
            @endforeach
        </ul>
    </div>
@endif
