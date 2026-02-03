document.getElementById('calc-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const startDate = document.getElementById('start_date').value;
    const days = document.getElementById('days').value;
    const type = document.querySelector('input[name="type"]:checked').value;
    const btn = document.querySelector('.btn-block');
    
    // Loading state
    btn.innerHTML = 'Calculando...';
    btn.disabled = true;

    try {
        const response = await fetch('api/calculate.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ startDate, days, type })
        });
        
        const data = await response.json();
        
        if (data.error === 'upgrade_required') {
            document.querySelector('.limit-alert').style.display = 'block';
            document.querySelector('.limit-alert').innerText = data.message;
            document.getElementById('results-area').style.display = 'none';
        } else if (data.error) {
            alert('Erro: ' + data.error);
        } else {
            // Show results
            document.querySelector('.limit-alert').style.display = 'none';
            document.getElementById('results-area').style.display = 'block';
            
            document.getElementById('result-date').innerText = data.description;
            // Update Log
            const logContainer = document.getElementById('log-details');
            logContainer.innerHTML = '';
            data.log.forEach(line => {
                const div = document.createElement('div');
                div.className = 'log-item';
                div.innerText = line;
                logContainer.appendChild(div);
            });

            // Update usage
            if (data.usage) {
                const remaining = data.usage.limit - data.usage.count;
                if (!data.usage.is_subscribed) {
                    // Update UI text about count if exist
                }
            }
            
            // Setup Google Calendar Link
            setupGoogleCalendar(data);
        }
        
    } catch (err) {
        alert("Ocorreu um erro na requisição.");
        console.error(err);
    } finally {
        btn.innerHTML = 'Calcular Prazo';
        btn.disabled = false;
    }
});

function setupGoogleCalendar(data) {
    // Construct Google Calendar Link
    // Format: https://calendar.google.com/calendar/render?action=TEMPLATE&text=TEXT&dates=START/END&details=DETAILS
    // Dates need to be YYYYMMDD format.
    
    const title = encodeURIComponent("Fim de Prazo Legal");
    // End Date + time (e.g. 09:00 AM to 10:00 AM)
    const endDateStr = data.end_date.replace(/-/g, '');
    const startDateTime = endDateStr + 'T090000';
    const endDateTime = endDateStr + 'T100000';
    
    const details = encodeURIComponent("Prazo processual calculado. " + data.description);
    
    const link = `https://calendar.google.com/calendar/render?action=TEMPLATE&text=${title}&dates=${startDateTime}/${endDateTime}&details=${details}`;
    
    const linkEl = document.getElementById('gcal-link');
    linkEl.href = link;
}
