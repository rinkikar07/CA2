/**
 * HIM - Cycle Tracker Calendar JS
 */
document.addEventListener('DOMContentLoaded', function() {
    let currentMonth = new Date().getMonth();
    let currentYear = new Date().getFullYear();
    
    const calendarGrid = document.getElementById('calendarGrid');
    const calendarTitle = document.getElementById('calendarTitle');
    const prevBtn = document.getElementById('prevMonth');
    const nextBtn = document.getElementById('nextMonth');
    
    const months = ['January','February','March','April','May','June','July','August','September','October','November','December'];
    
    function getPhaseForDay(date) {
        const start = new Date(lastPeriodStart);
        const diffTime = date.getTime() - start.getTime();
        const diffDays = Math.floor(diffTime / (1000 * 60 * 60 * 24));
        const dayInCycle = ((diffDays % cycleLength) + cycleLength) % cycleLength + 1;
        
        if (dayInCycle <= periodLength) return 'menstrual';
        if (dayInCycle <= Math.round(cycleLength * 0.45)) return 'follicular';
        if (dayInCycle <= Math.round(cycleLength * 0.55)) return 'ovulation';
        return 'luteal';
    }
    
    function isLoggedPeriodDay(dateStr) {
        return periodData.some(log => {
            const start = log.start_date;
            const end = log.end_date || log.start_date;
            return dateStr >= start && dateStr <= end;
        });
    }
    
    function hasSymptom(dateStr) {
        return symptomData.some(s => s.log_date === dateStr);
    }
    
    function isPredicted(date) {
        const start = new Date(lastPeriodStart);
        const diffDays = Math.floor((date - start) / (1000 * 60 * 60 * 24));
        if (diffDays < 0) return false;
        const dayInCycle = (diffDays % cycleLength) + 1;
        return dayInCycle <= periodLength && date > new Date();
    }
    
    function renderCalendar() {
        const firstDay = new Date(currentYear, currentMonth, 1).getDay();
        const daysInMonth = new Date(currentYear, currentMonth + 1, 0).getDate();
        const today = new Date();
        
        calendarTitle.textContent = `${months[currentMonth]} ${currentYear}`;
        calendarGrid.innerHTML = '';
        
        // Empty cells before first day
        for (let i = 0; i < firstDay; i++) {
            const cell = document.createElement('div');
            cell.className = 'cal-day empty';
            calendarGrid.appendChild(cell);
        }
        
        // Days of month
        for (let day = 1; day <= daysInMonth; day++) {
            const cell = document.createElement('div');
            const date = new Date(currentYear, currentMonth, day);
            const dateStr = date.toISOString().split('T')[0];
            
            cell.className = 'cal-day';
            cell.textContent = day;
            
            // Today
            if (day === today.getDate() && currentMonth === today.getMonth() && currentYear === today.getFullYear()) {
                cell.classList.add('today');
            }
            
            // Logged period
            if (isLoggedPeriodDay(dateStr)) {
                cell.classList.add('menstrual');
            } else if (isPredicted(date)) {
                cell.classList.add('predicted');
            } else {
                const phase = getPhaseForDay(date);
                cell.classList.add(phase);
            }
            
            // Symptom dot
            if (hasSymptom(dateStr)) {
                const dot = document.createElement('span');
                dot.className = 'symptom-dot';
                cell.appendChild(dot);
            }
            
            calendarGrid.appendChild(cell);
        }
    }
    
    prevBtn.addEventListener('click', () => {
        currentMonth--;
        if (currentMonth < 0) { currentMonth = 11; currentYear--; }
        renderCalendar();
    });
    
    nextBtn.addEventListener('click', () => {
        currentMonth++;
        if (currentMonth > 11) { currentMonth = 0; currentYear++; }
        renderCalendar();
    });
    
    renderCalendar();
});
