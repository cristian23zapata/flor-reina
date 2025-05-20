// Validación de formulario de contacto
function validarTelefono(input) {
    // Elimina cualquier caracter que no sea número
    input.value = input.value.replace(/[^0-9]/g, '');
    
    // Limita a 10 dígitos
    if (input.value.length > 10) {
        input.value = input.value.slice(0, 10);
    }
    
    // Valida el patrón (opcional, ya que el pattern de HTML5 lo hace)
    const telefonoValido = /^[0-9]{10}$/.test(input.value);
    input.setCustomValidity(telefonoValido ? '' : 'Numero de teléfono inválido');
}
