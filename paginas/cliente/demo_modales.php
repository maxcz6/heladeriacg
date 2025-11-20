<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Demo - Modales Modernos</title>
    <link rel="stylesheet" href="/heladeriacg/css/cliente/modernos_estilos_cliente.css">
    <link rel="stylesheet" href="/heladeriacg/css/cliente/modales.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }

        .demo-container {
            background: white;
            border-radius: 20px;
            padding: 3rem;
            max-width: 800px;
            width: 100%;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }

        h1 {
            text-align: center;
            color: #1f2937;
            margin-bottom: 0.5rem;
        }

        .subtitle {
            text-align: center;
            color: #6b7280;
            margin-bottom: 3rem;
        }

        .demo-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .demo-btn {
            padding: 1rem 1.5rem;
            border: none;
            border-radius: 12px;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .demo-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
        }

        .btn-success {
            background: linear-gradient(135deg, #10b981, #059669);
        }

        .btn-error {
            background: linear-gradient(135deg, #ef4444, #dc2626);
        }

        .btn-warning {
            background: linear-gradient(135deg, #f59e0b, #d97706);
        }

        .btn-info {
            background: linear-gradient(135deg, #0ea5e9, #0284c7);
        }

        .btn-confirm {
            background: linear-gradient(135deg, #8b5cf6, #7c3aed);
        }

        .btn-loading {
            background: linear-gradient(135deg, #6366f1, #4f46e5);
        }

        .section-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: #374151;
            margin: 2rem 0 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #e5e7eb;
        }
    </style>
</head>
<body>
    <div class="demo-container">
        <h1>🎨 Modales Modernos</h1>
        <p class="subtitle">Sistema de alertas y confirmaciones mejorado para Concelato</p>

        <div class="section-title">Alertas Básicas</div>
        <div class="demo-grid">
            <button class="demo-btn btn-success" onclick="testSuccess()">
                <i class="fas fa-check-circle"></i>
                Éxito
            </button>
            <button class="demo-btn btn-error" onclick="testError()">
                <i class="fas fa-times-circle"></i>
                Error
            </button>
            <button class="demo-btn btn-warning" onclick="testWarning()">
                <i class="fas fa-exclamation-triangle"></i>
                Advertencia
            </button>
            <button class="demo-btn btn-info" onclick="testInfo()">
                <i class="fas fa-info-circle"></i>
                Información
            </button>
        </div>

        <div class="section-title">Confirmaciones</div>
        <div class="demo-grid">
            <button class="demo-btn btn-confirm" onclick="testConfirm()">
                <i class="fas fa-question-circle"></i>
                Confirmar Acción
            </button>
            <button class="demo-btn btn-confirm" onclick="testDelete()">
                <i class="fas fa-trash"></i>
                Eliminar Item
            </button>
        </div>

        <div class="section-title">Estados Especiales</div>
        <div class="demo-grid">
            <button class="demo-btn btn-loading" onclick="testLoading()">
                <i class="fas fa-spinner"></i>
                Cargando
            </button>
            <button class="demo-btn btn-info" onclick="testChained()">
                <i class="fas fa-link"></i>
                Modales Encadenados
            </button>
        </div>
    </div>

    <script src="/heladeriacg/js/cliente/modales.js"></script>
    <script>
        function testSuccess() {
            ModalCliente.success(
                '¡Tu pedido ha sido confirmado exitosamente!\n\nPedido #12345\nTotal: S/. 25.50',
                '¡Pedido Confirmado!'
            );
        }

        function testError() {
            ModalCliente.error(
                'No se pudo procesar tu solicitud. Por favor, verifica tu conexión e intenta nuevamente.',
                'Error al Procesar'
            );
        }

        function testWarning() {
            ModalCliente.warning(
                'Tu carrito está vacío. Agrega productos antes de confirmar el pedido.',
                'Carrito Vacío'
            );
        }

        function testInfo() {
            ModalCliente.info(
                'Los pedidos se procesan de lunes a sábado de 9:00 AM a 6:00 PM.',
                'Horario de Atención'
            );
        }

        async function testConfirm() {
            const confirmed = await ModalCliente.confirm(
                '¿Estás seguro de que deseas cerrar tu sesión actual?',
                '¿Cerrar Sesión?'
            );
            
            if (confirmed) {
                ModalCliente.success('Sesión cerrada correctamente.', 'Hasta Pronto');
            } else {
                ModalCliente.info('Operación cancelada.', 'Cancelado');
            }
        }

        async function testDelete() {
            const confirmed = await ModalCliente.confirm(
                'Esta acción no se puede deshacer. ¿Deseas continuar?',
                '¿Eliminar Producto?'
            );
            
            if (confirmed) {
                ModalCliente.success('Producto eliminado correctamente.', 'Eliminado');
            }
        }

        function testLoading() {
            const loader = ModalCliente.loading('Procesando tu pedido...');
            
            setTimeout(() => {
                loader.close();
                ModalCliente.success('¡Pedido procesado con éxito!', '¡Listo!');
            }, 3000);
        }

        async function testChained() {
            await ModalCliente.info(
                'Este es el primer modal de una secuencia.',
                'Paso 1'
            );
            
            const confirmed = await ModalCliente.confirm(
                '¿Deseas continuar al siguiente paso?',
                'Paso 2'
            );
            
            if (confirmed) {
                await ModalCliente.success(
                    '¡Has completado la secuencia!',
                    'Paso 3 - Completado'
                );
            }
        }
    </script>
</body>
</html>
