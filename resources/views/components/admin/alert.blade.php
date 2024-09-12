@props([
    'type' => 'primary',
    'dismissible' => false,
])

<div {{ $attributes->merge(['class' => 'alert alert-' . $type . ($dismissible ? ' alert-dismissible fade show' : '')]) }}>
    @if($dismissible)
        <button type="button" class="close" data-dismiss="alert">×</button>
    @endif
    {{ $slot }}
</div>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script>
$(document).ready(function(){
    $(".alert .close").click(function(){
        $(this).parent().hide();
    });
});
</script>