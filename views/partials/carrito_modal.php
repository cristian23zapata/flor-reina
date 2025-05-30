<?php
// Este archivo contiene el HTML para el modal del carrito,
// el toast de notificacion y el JavaScript para su funcionamiento.
// No debe ser accedido directamente, solo incluido.
?>

<div class="modal fade" id="modalCarrito" tabindex="-1" aria-labelledby="modalCarritoLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-custom-pink text-white"> <h5 class="modal-title" id="modalCarritoLabel">
                    <i class="bi bi-bag"></i> Mi Carrito
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div id="carrito-vacio" class="text-center">
                    <p>Tu carrito está vacío.</p>
                </div>
                <div id="carrito-contenido" style="display: none;">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th>Cantidad</th>
                                <th>Precio</th>
                                <th>Subtotal</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody id="carrito-items">
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="3" class="text-end fw-bold">Total:</td>
                                <td class="text-end fw-bold" id="carrito-total"></td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-danger-custom" id="vaciar-carrito">Vaciar Carrito</button> <button type="button" class="btn btn-primary-custom" id="btn-pagar-modal">Pagar</button> </div>
        </div>
    </div>
</div>

<div class="toast-container position-fixed bottom-0 end-0 p-3">
    <div id="toast-agregado" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-header bg-custom-pink text-white"> <strong class="me-auto">Éxito</strong>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Cerrar"></button>
        </div>
        <div class="toast-body">
            Producto agregado al carrito!
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const carritoContador = document.getElementById('carrito-contador');
        const carritoItems = document.getElementById('carrito-items');
        const carritoTotal = document.getElementById('carrito-total');
        const carritoVacio = document.getElementById('carrito-vacio');
        const carritoContenido = document.getElementById('carrito-contenido');

        let carrito = JSON.parse(localStorage.getItem('carrito')) || [];

        function actualizarContador() {
            let totalItems = carrito.reduce((sum, item) => sum + item.cantidad, 0);
            if (carritoContador) { // Asegura que el contador existe en la página
                carritoContador.textContent = totalItems;
                carritoContador.style.display = totalItems > 0 ? 'inline-block' : 'none';
            }
        }

        function eliminarItem(index) {
            carrito.splice(index, 1);
            localStorage.setItem('carrito', JSON.stringify(carrito));
            renderizarCarrito();
            actualizarContador();
        }

        function vaciarCarrito() {
            carrito = [];
            localStorage.removeItem('carrito');
            renderizarCarrito();
            actualizarContador();
        }

        const vaciarCarritoBtn = document.getElementById('vaciar-carrito');
        if (vaciarCarritoBtn) {
            vaciarCarritoBtn.addEventListener('click', vaciarCarrito);
        }

        document.addEventListener('click', function(e) {
            if (e.target && e.target.classList.contains('eliminar-item')) {
                const index = e.target.dataset.index;
                eliminarItem(index);
            }
        });

        function renderizarCarrito() {
            if (carrito.length === 0) {
                if (carritoVacio) carritoVacio.style.display = 'block';
                if (carritoContenido) carritoContenido.style.display = 'none';
                const btnPagarModal = document.getElementById('btn-pagar-modal');
                const btnVaciarCarrito = document.getElementById('vaciar-carrito');
                if (btnPagarModal) btnPagarModal.style.display = 'none';
                if (btnVaciarCarrito) btnVaciarCarrito.style.display = 'none';
            } else {
                if (carritoVacio) carritoVacio.style.display = 'none';
                if (carritoContenido) carritoContenido.style.display = 'block';
                const btnPagarModal = document.getElementById('btn-pagar-modal');
                const btnVaciarCarrito = document.getElementById('vaciar-carrito');
                if (btnPagarModal) btnPagarModal.style.display = 'inline-block';
                if (btnVaciarCarrito) btnVaciarCarrito.style.display = 'inline-block';
                
                if (carritoItems) {
                    carritoItems.innerHTML = '';
                    let total = 0;
                    carrito.forEach((item, index) => {
                        const subtotal = item.precio * item.cantidad;
                        total += subtotal;
                        const tr = document.createElement('tr');
                        tr.innerHTML = `
                            <td>
                                <div class="d-flex align-items-center">
                                    <img src="../${item.imagen}" alt="${item.nombre}" class="me-2" style="width: 60px; height: 60px; object-fit: cover;">
                                    <span class="text-truncate" style="max-width: 150px;">${item.nombre}</span>
                                </div>
                            </td>
                            <td>
                                <div class="input-group" style="min-width: 140px;">
                                    <button class="btn btn-outline-secondary decrementar-cantidad py-1" type="button" data-index="${index}">-</button>
                                    <input type="number" class="form-control text-center py-1" value="${item.cantidad}" min="1" max="${item.stock}" data-index="${index}">
                                    <button class="btn btn-outline-secondary incrementar-cantidad py-1" type="button" data-index="${index}">+</button>
                                </div>
                            </td>
                            <td class="text-end align-middle">$${item.precio.toFixed(2)}</td>
                            <td class="text-end align-middle">$${subtotal.toFixed(2)}</td>
                            <td class="text-center align-middle">
                                <button class="btn btn-sm btn-outline-danger p-1 eliminar-item" data-index="${index}">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        `;
                        carritoItems.appendChild(tr);
                    });
                }
                if (carritoTotal) carritoTotal.textContent = `$${total.toFixed(2)}`;
            }
        }

        document.addEventListener('change', function(e) {
            if (e.target && e.target.matches('.input-group input[type="number"]')) {
                const input = e.target;
                const index = input.closest('.input-group').querySelector('button').dataset.index;
                const nuevaCantidad = parseInt(input.value);
                if (nuevaCantidad > 0 && nuevaCantidad <= carrito[index].stock) {
                    carrito[index].cantidad = nuevaCantidad;
                    localStorage.setItem('carrito', JSON.stringify(carrito));
                    renderizarCarrito();
                    actualizarContador();
                } else {
                    alert('La cantidad no puede ser mayor al stock disponible');
                    input.value = carrito[index].cantidad;
                }
            }
        });

        document.addEventListener('click', function(e) {
            if (e.target && e.target.classList.contains('incrementar-cantidad')) {
                const index = e.target.dataset.index;
                const input = e.target.previousElementSibling;
                const currentQuantity = parseInt(input.value);
                if (currentQuantity < carrito[index].stock) {
                    input.value = currentQuantity + 1;
                    carrito[index].cantidad = currentQuantity + 1;
                    localStorage.setItem('carrito', JSON.stringify(carrito));
                    renderizarCarrito();
                    actualizarContador();
                }
            }
            if (e.target && e.target.classList.contains('decrementar-cantidad')) {
                const index = e.target.dataset.index;
                const input = e.target.nextElementSibling;
                const currentQuantity = parseInt(input.value);
                if (currentQuantity > 1) {
                    input.value = currentQuantity - 1;
                    carrito[index].cantidad = currentQuantity - 1;
                    localStorage.setItem('carrito', JSON.stringify(carrito));
                    renderizarCarrito();
                    actualizarContador();
                }
            }
        });

        const modalCarritoElement = document.getElementById('modalCarrito');
        if (modalCarritoElement) {
            modalCarritoElement.addEventListener('show.bs.modal', function() {
                renderizarCarrito();
            });
        }
        
        actualizarContador();

        const btnPagarModal = document.getElementById('btn-pagar-modal');
        if (btnPagarModal) {
            btnPagarModal.addEventListener('click', function() {
                const carritoData = localStorage.getItem('carrito');
                if (carritoData && JSON.parse(carritoData).length > 0) {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = '../views/pagar.php';
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'carrito';
                    input.value = carritoData;
                    form.appendChild(input);
                    document.body.appendChild(form);
                    form.submit();
                } else {
                    alert('Tu carrito está vacío. Agrega productos antes de pagar.');
                }
            });
        }
    });
</script>