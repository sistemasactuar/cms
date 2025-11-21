<div wire:ignore class="p-2 bg-white border rounded">
    <canvas id="firmaCanvas" width="400" height="200" class="border border-gray-300 rounded"></canvas>
    <div class="flex gap-2 mt-2">
        <button type="button" id="limpiarFirma" class="px-3 py-1 bg-gray-200 rounded hover:bg-gray-300">Limpiar</button>
    </div>
    <input type="hidden" id="firmaInput" name="firma">
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const canvas = document.getElementById('firmaCanvas');
    const ctx = canvas.getContext('2d');
    let dibujando = false;

    canvas.addEventListener('mousedown', e => { dibujando = true; ctx.beginPath(); ctx.moveTo(e.offsetX, e.offsetY); });
    canvas.addEventListener('mouseup', () => dibujando = false);
    canvas.addEventListener('mouseout', () => dibujando = false);
    canvas.addEventListener('mousemove', e => {
        if (!dibujando) return;
        ctx.lineWidth = 2;
        ctx.lineCap = 'round';
        ctx.strokeStyle = '#000';
        ctx.lineTo(e.offsetX, e.offsetY);
        ctx.stroke();
    });

    document.getElementById('limpiarFirma').addEventListener('click', () => {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        document.getElementById('firmaInput').value = '';
    });

    // Guardar base64 antes de enviar
    document.querySelector('form').addEventListener('submit', () => {
        document.getElementById('firmaInput').value = canvas.toDataURL('image/png');
    });
});
</script>
