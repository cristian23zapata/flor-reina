document.addEventListener('DOMContentLoaded', function() {
    // Inicializar carrito desde localStorage
    let carrito = JSON.parse(localStorage.getItem('carrito')) || [];
    
    // Actualizar contador del carrito
    function actualizarContador() {
      const totalItems = carrito.reduce((total, item) => total + item.cantidad, 0);
      const contador = document.getElementById('carrito-contador');
      
      if (totalItems > 0) {
        contador.textContent = totalItems;
        contador.style.display = 'block';
      } else {
        contador.style.display = 'none';
      }
    }
    
    // Renderizar carrito en el modal
    function renderizarCarrito() {
      const carritoItems = document.getElementById('carrito-items');
      const carritoVacio = document.getElementById('carrito-vacio');
      const carritoContenido = document.getElementById('carrito-contenido');
      const carritoTotal = document.getElementById('carrito-total');
      
      if (carrito.length === 0) {
        carritoVacio.style.display = 'block';
        carritoContenido.style.display = 'none';
        document.getElementById('btn-pagar').style.display = 'none';
        document.getElementById('vaciar-carrito').style.display = 'none';
      } else {
        carritoVacio.style.display = 'none';
        carritoContenido.style.display = 'block';
        document.getElementById('btn-pagar').style.display = 'inline-block';
        document.getElementById('vaciar-carrito').style.display = 'inline-block';
        
        carritoItems.innerHTML = '';
        let total = 0;
        
        carrito.forEach((item, index) => {
          const subtotal = item.precio * item.cantidad;
          total += subtotal;
          
          const tr = document.createElement('tr');
tr.innerHTML = `
  <td>
    <div class="d-flex align-items-center">
      <img src="${item.imagen}" alt="${item.nombre}" class="me-2" style="width: 60px; height: 60px; object-fit: cover;">
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
        
        carritoTotal.textContent = `$${total.toFixed(2)}`;
      }
    }
    
    // Manejar el formulario de agregar al carrito
    document.addEventListener('submit', function(e) {
      if (e.target && e.target.classList.contains('agregar-carrito-form')) {
        e.preventDefault();
        
        const form = e.target;
        const id = form.querySelector('input[name="id"]').value;
        const nombre = form.querySelector('input[name="nombre"]').value;
        const precio = parseFloat(form.querySelector('input[name="precio"]').value);
        const imagen = form.querySelector('input[name="imagen"]').value;
        const stock = parseInt(form.querySelector('input[name="stock"]').value);
        const cantidad = parseInt(form.querySelector('input[name="cantidad"]').value);
        
        // Verificar si el producto ya está en el carrito
        const itemExistente = carrito.find(item => item.id === id);
        
        if (itemExistente) {
          // Actualizar cantidad si no supera el stock
          const nuevaCantidad = itemExistente.cantidad + cantidad;
          if (nuevaCantidad <= stock) {
            itemExistente.cantidad = nuevaCantidad;
          } else {
            alert('No hay suficiente stock disponible');
            return;
          }
        } else {
          // Agregar nuevo item al carrito
          carrito.push({
            id,
            nombre,
            precio,
            imagen,
            cantidad,
            stock
          });
        }
        
        // Guardar en localStorage y actualizar UI
        localStorage.setItem('carrito', JSON.stringify(carrito));
        actualizarContador();
        renderizarCarrito();
        
        // Mostrar notificación
        const toast = new bootstrap.Toast(document.getElementById('toast-agregado'));
        toast.show();
      }
    });
    
    // Incrementar/decrementar cantidad en el modal de producto
    document.addEventListener('click', function(e) {
      // Botones + y - en el modal de producto
      if (e.target && (e.target.id === 'incrementar' || e.target.id === 'decrementar')) {
        const input = e.target.closest('.input-group').querySelector('input');
        let value = parseInt(input.value);
        
        if (e.target.id === 'incrementar' && value < parseInt(input.max)) {
          input.value = value + 1;
        } else if (e.target.id === 'decrementar' && value > parseInt(input.min)) {
          input.value = value - 1;
        }
      }
      
      // Eliminar item del carrito
      if (e.target && (e.target.classList.contains('eliminar-item') || e.target.closest('.eliminar-item'))) {
        const button = e.target.classList.contains('eliminar-item') ? e.target : e.target.closest('.eliminar-item');
        const index = button.dataset.index;
        carrito.splice(index, 1);
        localStorage.setItem('carrito', JSON.stringify(carrito));
        actualizarContador();
        renderizarCarrito();
      }
      
      // Vaciar carrito
      if (e.target && e.target.id === 'vaciar-carrito') {
        if (confirm('¿Estás seguro de que quieres vaciar el carrito?')) {
          carrito = [];
          localStorage.setItem('carrito', JSON.stringify(carrito));
          actualizarContador();
          renderizarCarrito();
        }
      }
      
      // Incrementar cantidad en el carrito
      if (e.target && (e.target.classList.contains('incrementar-cantidad') || e.target.closest('.incrementar-cantidad'))) {
        const button = e.target.classList.contains('incrementar-cantidad') ? e.target : e.target.closest('.incrementar-cantidad');
        const index = button.dataset.index;
        const input = button.closest('.input-group').querySelector('input');
        
        if (carrito[index].cantidad < carrito[index].stock) {
          carrito[index].cantidad++;
          input.value = carrito[index].cantidad;
          localStorage.setItem('carrito', JSON.stringify(carrito));
          renderizarCarrito();
          actualizarContador();
        }
      }
      
      // Decrementar cantidad en el carrito
      if (e.target && (e.target.classList.contains('decrementar-cantidad') || e.target.closest('.decrementar-cantidad'))) {
        const button = e.target.classList.contains('decrementar-cantidad') ? e.target : e.target.closest('.decrementar-cantidad');
        const index = button.dataset.index;
        const input = button.closest('.input-group').querySelector('input');
        
        if (carrito[index].cantidad > 1) {
          carrito[index].cantidad--;
          input.value = carrito[index].cantidad;
          localStorage.setItem('carrito', JSON.stringify(carrito));
          renderizarCarrito();
          actualizarContador();
        }
      }
    });
    
    // Actualizar cantidad desde el input en el carrito
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
    
    // Renderizar carrito cuando se abre el modal
    document.getElementById('modalCarrito').addEventListener('show.bs.modal', function() {
      renderizarCarrito();
    });
    
    // Inicializar contador al cargar la página
    actualizarContador();
  });
  