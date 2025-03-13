// Inicialización cuando el DOM está listo
document.addEventListener("DOMContentLoaded", function() {
    // Referencias a elementos del formulario
    const formulario = document.getElementById("productoForm");
    const codigoInput = document.getElementById("codigo");
    const nombreInput = document.getElementById("nombre");
    const bodegaSelect = document.getElementById("bodegaSelect");
    const sucursalSelect = document.getElementById("sucursalSelect");
    const monedaSelect = document.getElementById("moneda");
    const precioInput = document.getElementById("precio");
    const descripcionTextarea = document.getElementById("descripcion");
    
    // Patrones de validación
    const codigoRegex = /^(?=.*[a-zA-Z])(?=.*\d)[a-zA-Z0-9]{5,15}$/;
    const precioRegex = /^\d+(\.\d{1,2})?$/;

    // Validación del código del producto
    async function validarCodigo(codigo) {
        const codigoTrim = codigo.trim();
        
        if (!codigoTrim) {
            alert("El código del producto no puede estar en blanco.");
            return false;
        }
        
        if (!(/(?=.*[a-zA-Z])(?=.*\d)/).test(codigoTrim)) {
            alert("El código del producto debe contener letras y números");
            return false;
        }

        if (codigoTrim.length < 5 || codigoTrim.length > 15) {
            alert("El código del producto debe tener entre 5 y 15 caracteres.");
            return false;
        }

        if (!(/^[a-zA-Z0-9]+$/).test(codigoTrim)) {
            alert("El código del producto debe contener letras y números");
            return false;
        }

        // Verificación de código único en base de datos
        try {
            const response = await fetch('guardar.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `verificar_codigo=${encodeURIComponent(codigoTrim)}`
            });
            
            const data = await response.json();
            if (!data.disponible) {
                alert("El código del producto ya está registrado.");
                return false;
            }
        } catch (error) {
            console.error("Error al verificar código:", error);
            return false;
        }

        return true;
    }

    // Validación del nombre del producto
    function validarNombre() {
        const nombre = nombreInput.value.trim();
        
        if (!nombre) {
            alert("El nombre del producto no puede estar en blanco.");
            return false;
        }
        
        if (nombre.length < 2 || nombre.length > 50) {
            alert("El nombre del producto debe tener entre 2 y 50 caracteres.");
            return false;
        }
        
        return true;
    }

    // Validación del precio
    function validarPrecio() {
        const precio = precioInput.value.trim();
        
        if (!precio) {
            alert("El precio del producto no puede estar en blanco.");
            return false;
        }
        
        if (!precioRegex.test(precio)) {
            alert("El precio del producto debe ser un número positivo con hasta dos decimales.");
            return false;
        }
        
        return true;
    }

    // Validación de materiales seleccionados
    function validarMateriales() {
        const materialesSeleccionados = document.querySelectorAll('input[name="material[]"]:checked');
        
        if (materialesSeleccionados.length < 2) {
            alert("Debe seleccionar al menos dos materiales para el producto.");
            return false;
        }
        
        return true;
    }

    // Validación de bodega
    function validarBodega() {
        if (!bodegaSelect.value) {
            alert("Debe seleccionar una bodega.");
            return false;
        }
        return true;
    }

    // Validación de sucursal
    function validarSucursal() {
        if (!sucursalSelect.value) {
            alert("Debe seleccionar una sucursal para la bodega seleccionada.");
            return false;
        }
        return true;
    }

    // Validación de moneda
    function validarMoneda() {
        if (!monedaSelect.value) {
            alert("Debe seleccionar una moneda para el producto.");
            return false;
        }
        return true;
    }

    // Validación de descripción
    function validarDescripcion() {
        const descripcion = descripcionTextarea.value.trim();
        
        if (!descripcion) {
            alert("La descripción del producto no puede estar en blanco.");
            return false;
        }
        
        if (descripcion.length < 10 || descripcion.length > 1000) {
            alert("La descripción del producto debe tener entre 10 y 1000 caracteres.");
            return false;
        }
        
        return true;
    }

    // Carga dinámica de sucursales por AJAX
    function cargarSucursales(bodegaSelect) {
        const bodegaId = bodegaSelect.options[bodegaSelect.selectedIndex]?.dataset?.id;
        
        if (bodegaId) {
            fetch(`obtener_sucursales.php?bodega_id=${bodegaId}`)
                .then(response => response.json())
                .then(data => {
                    sucursalSelect.innerHTML = '<option value=""></option>';
                    
                    if (data.sucursales && data.sucursales.length > 0) {
                        data.sucursales.forEach(sucursal => {
                            const option = document.createElement("option");
                            option.value = sucursal.nombre;
                            option.textContent = sucursal.nombre;
                            sucursalSelect.appendChild(option);
                        });
                    }
                })
                .catch(error => {
                    console.error("Error al cargar sucursales:", error);
                    sucursalSelect.innerHTML = '<option value=""></option>';
                });
        } else {
            sucursalSelect.innerHTML = '<option value=""></option>';
        }
    }

    // Eventos
    bodegaSelect.addEventListener("change", function() {
        cargarSucursales(this);
    });

    // Manejo del envío del formulario
    formulario.addEventListener("submit", async function(e) {
        e.preventDefault();

        // Ejecutar todas las validaciones
        const validaciones = [
            await validarCodigo(codigoInput.value),
            validarNombre(),
            validarBodega(),
            validarSucursal(),
            validarMoneda(),
            validarPrecio(),
            validarMateriales(),
            validarDescripcion()
        ];

        // Si todas las validaciones son exitosas, enviar datos
        if (validaciones.every(v => v === true)) {
            const formData = new FormData(this);

            // Envío de datos por AJAX
            fetch("guardar.php", {
                method: "POST",
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.exito) {
                    alert(data.mensaje);
                    formulario.reset();
                    sucursalSelect.innerHTML = '<option value=""></option>';
                } else {
                    alert(data.mensaje);
                }
            })
            .catch(error => {
                console.error("Error:", error);
                alert("Hubo un error al intentar enviar los datos.");
            });
        }
    });
});
