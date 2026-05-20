(() => {
    const { jsPDF } = window.jspdf || {};

    if (!jsPDF) {
        return;
    }

    const toDataUrl = async (url) => {
        const response = await fetch(url, { mode: 'cors' });
        if (!response.ok) throw new Error('No se pudo cargar la imagen QR');
        const blob = await response.blob();
        return await new Promise((resolve, reject) => {
            const reader = new FileReader();
            reader.onloadend = () => resolve(reader.result);
            reader.onerror = () => reject(new Error('No se pudo leer la imagen QR'));
            reader.readAsDataURL(blob);
        });
    };

    const sanitizeName = (value) => {
        return value
            .toLowerCase()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .replace(/[^a-z0-9]+/g, '-')
            .replace(/(^-|-$)/g, '') || 'entrada';
    };

    const getRequestHeaders = () => {
        const token = localStorage.getItem('sanctum_token');
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
        return {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Authorization': token ? `Bearer ${token}` : '',
        };
    };

    const hexToRgb = (hex) => {
        const result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
        return result ? [
            parseInt(result[1], 16),
            parseInt(result[2], 16),
            parseInt(result[3], 16)
        ] : [0, 0, 0];
    };

    const generatePDF = async (data) => {
        const { evento, fecha, hora, asiento, entrada, precio, codigo } = data;

        const pdf = new jsPDF({ unit: 'mm', format: 'a4' });
        const W = 210;
        const H = 297;

        // ── Fondo negro total ──
        pdf.setFillColor(10, 10, 10);
        pdf.rect(0, 0, W, H, 'F');

        // ── Franja superior dorada ──
        pdf.setFillColor(212, 175, 55);
        pdf.rect(0, 0, W, 3, 'F');

        // ── Franja inferior dorada ──
        pdf.setFillColor(212, 175, 55);
        pdf.rect(0, H - 3, W, 3, 'F');

        // ── Línea vertical decorativa izquierda ──
        pdf.setFillColor(212, 175, 55);
        pdf.rect(14, 0, 0.5, H, 'F');

        // ── Línea vertical decorativa derecha ──
        pdf.setFillColor(212, 175, 55);
        pdf.rect(W - 14.5, 0, 0.5, H, 'F');

        // ── Texto "CESAR ARENA" ──
        pdf.setFont('helvetica', 'bold');
        pdf.setFontSize(10);
        pdf.setTextColor(212, 175, 55);
        pdf.setCharSpace(4);
        pdf.text('CESAR ARENA', W / 2, 20, { align: 'center' });
        pdf.setCharSpace(0);

        // ── Línea divisoria bajo el título ──
        pdf.setDrawColor(212, 175, 55);
        pdf.setLineWidth(0.3);
        pdf.line(20, 24, W - 20, 24);

        // ── Nombre del evento ──
        pdf.setFont('helvetica', 'bold');
        pdf.setFontSize(28);
        pdf.setTextColor(255, 255, 255);
        const eventoLines = pdf.splitTextToSize(evento.toUpperCase(), W - 40);
        pdf.text(eventoLines, W / 2, 42, { align: 'center' });

        const afterEvento = 42 + (eventoLines.length - 1) * 10;

        // ── Subtítulo / recinto ──
        pdf.setFont('helvetica', 'normal');
        pdf.setFontSize(9);
        pdf.setTextColor(150, 150, 150);
        pdf.setCharSpace(2);
        pdf.text('ENTRADA OFICIAL', W / 2, afterEvento + 10, { align: 'center' });
        pdf.setCharSpace(0);

        // ── Línea divisoria ──
        pdf.setDrawColor(50, 50, 50);
        pdf.setLineWidth(0.3);
        pdf.line(20, afterEvento + 16, W - 20, afterEvento + 16);

        // ── Bloque de datos: fecha / hora / asiento / precio ──
        const dataY = afterEvento + 30;
        const colW = (W - 40) / 2;

        const drawDataBlock = (label, value, x, y) => {
            pdf.setFont('helvetica', 'normal');
            pdf.setFontSize(7.5);
            pdf.setTextColor(150, 150, 150);
            pdf.setCharSpace(1.5);
            pdf.text(label, x, y);
            pdf.setCharSpace(0);

            pdf.setFont('helvetica', 'bold');
            pdf.setFontSize(13);
            pdf.setTextColor(255, 255, 255);
            pdf.text(value, x, y + 6);
        };

        drawDataBlock('FECHA', fecha, 20, dataY);
        drawDataBlock('HORA', hora, 20 + colW, dataY);
        drawDataBlock('ASIENTO', asiento, 20, dataY + 20);
        drawDataBlock('PRECIO', precio, 20 + colW, dataY + 20);

        // ── Línea divisoria ──
        const qrSectionY = dataY + 46;
        pdf.setDrawColor(50, 50, 50);
        pdf.setLineWidth(0.3);
        pdf.line(20, qrSectionY - 8, W - 20, qrSectionY - 8);

        // ── QR ──
        const qrSize = 55;
        const qrX = (W - qrSize) / 2;

        try {
            const qrUrl = `https://quickchart.io/qr?text=${encodeURIComponent(codigo)}&size=256&margin=2&ecLevel=M&format=png&dark=FFFFFF&light=0A0A0A`;
            const qrDataUrl = await toDataUrl(qrUrl);
            pdf.addImage(qrDataUrl, 'PNG', qrX, qrSectionY, qrSize, qrSize);
        } catch {
            pdf.setFont('helvetica', 'normal');
            pdf.setFontSize(9);
            pdf.setTextColor(150, 150, 150);
            pdf.text('No se pudo generar el QR', W / 2, qrSectionY + 20, { align: 'center' });
        }

        // ── Código de entrada bajo el QR ──
        pdf.setFont('helvetica', 'normal');
        pdf.setFontSize(7);
        pdf.setTextColor(100, 100, 100);
        pdf.setCharSpace(1);
        pdf.text(codigo, W / 2, qrSectionY + qrSize + 6, { align: 'center' });
        pdf.setCharSpace(0);

        // ── Número de entrada (esquina inferior) ──
        pdf.setFont('helvetica', 'normal');
        pdf.setFontSize(8);
        pdf.setTextColor(80, 80, 80);
        pdf.text(`ENTRADA #${entrada}`, 20, H - 10);

        const safeEvento = sanitizeName(evento);
        pdf.save(`entrada-${safeEvento}-${entrada}.pdf`);
    };

    document.querySelectorAll('[data-ticket-download]').forEach((button) => {
        button.addEventListener('click', async () => {
            const evento = button.dataset.evento || 'Evento';
            const fecha = button.dataset.fecha || 'Por confirmar';
            const hora = button.dataset.hora || 'Por confirmar';
            const asiento = button.dataset.asiento || 'Asiento no disponible';
            const entrada = button.dataset.entrada || '-';
            const precioRaw = parseFloat((button.dataset.precioRaw || '0').replace(',', '.'));
            const precio = precioRaw > 0
                ? precioRaw.toLocaleString('es-ES', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' 20ac'
                : (button.dataset.precio || '-');
            const codigo = button.dataset.codigo || '-';

            const confirmacion = confirm(
                'ADVERTENCIA\n\n' +
                'Al descargar la entrada:\n' +
                'El estado cambiará a "Descargada" en la base de datos\n' +
                '¿Estás seguro de que quieres descargar?'
            );

            if (!confirmacion) return;

            try {
                const response = await fetch(`/api/entradas/${entrada}/descargar`, {
                    method: 'POST',
                    credentials: 'include',
                    headers: getRequestHeaders(),
                });

                if (response.status === 401) {
                    window.location.href = `/login?redirect=${encodeURIComponent(window.location.pathname)}`;
                    return;
                }

                if (!response.ok) {
                    const errorBody = await response.json().catch(() => null);
                    throw new Error(errorBody?.message || 'No se pudo marcar la entrada como descargada');
                }

                const ticketCard = button.closest('.ticket-card');
                const cancelButton = ticketCard?.querySelector('[data-ticket-cancel], .btn-cancelar-solicitud');
                if (cancelButton) cancelButton.remove();

            } catch (error) {
                alert('Error: ' + error.message);
                console.error('Error al marcar entrada como descargada:', error);
                return;
            }

            await generatePDF({ evento, fecha, hora, asiento, entrada, precio, codigo });
        });
    });

    document.querySelectorAll('[data-ticket-cancel]').forEach((button) => {
        button.addEventListener('click', async () => {
            const entrada = button.dataset.entrada;
            if (!entrada) return;

            const confirmacion = confirm(
                '¿Seguro que quieres cancelar esta compra?\n\n' +
                'Esta acción eliminará la entrada de tu cuenta.'
            );

            if (!confirmacion) return;

            try {
                const response = await fetch(`/api/entradas/${entrada}/cancelar`, {
                    method: 'DELETE',
                    credentials: 'include',
                    headers: getRequestHeaders(),
                });

                if (response.status === 401) {
                    window.location.href = `/login?redirect=${encodeURIComponent(window.location.pathname)}`;
                    return;
                }

                const body = await response.json().catch(() => null);

                if (!response.ok) {
                    throw new Error(body?.message || 'No se pudo cancelar la compra');
                }

                const ticketCard = button.closest('.ticket-card');
                if (ticketCard) ticketCard.remove();

                alert(body?.message || 'Compra cancelada correctamente.');
            } catch (error) {
                alert('Error: ' + error.message);
                console.error('Error al cancelar compra:', error);
            }
        });
    });
})();