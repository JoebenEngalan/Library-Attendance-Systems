function updateClock() {
    const now = new Date();
    let hours = now.getHours();
    let minutes = now.getMinutes();
    let seconds = now.getSeconds();

    let ampm = hours >= 12 ? 'PM' : 'AM';
    hours = hours % 12;
    hours = hours ? hours : 12; // The hour '0' (midnight) should be '12'

    minutes = minutes < 10 ? '0' + minutes : minutes;
    seconds = seconds < 10 ? '0' + seconds : seconds;

    let timeString = 'Time: '+ hours + ':' + minutes + ':' + seconds + ' ' + ampm;
    document.getElementById('clock').innerHTML = timeString;
}

function displayTodaysDate() {
    const today = new Date();
    let month = today.getMonth() + 1; // getMonth() is zero-indexed, so add 1
    let day = today.getDate();
    const year = today.getFullYear();

    // Add leading zero if month or day is less than 10
    if (month < 10) {
        month = '0' + month;
    }
    if (day < 10) {
        day = '0' + day;
    }

    const formattedDate = 'Date: '+`${month}/${day}/${year}`;
    document.getElementById('date').textContent = formattedDate;
}
// Call the function when the page loads
window.onload = displayTodaysDate;

// Call initially to display time immediately
updateClock();

// Update every second
setInterval(updateClock, 1000);