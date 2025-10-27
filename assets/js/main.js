/*
 * Main JavaScript file for SecureShop
 * ====================================
 * Contains:
 * 1. "Added to Cart" Toast Notification Handler
 * 2. "Special Offer" Countdown Timer
 */

// Wait for the DOM to be fully loaded before running scripts
document.addEventListener('DOMContentLoaded', () => {

    /*
     * 1. Toast Notification Handler
     */
    const toast = document.getElementById('toast-notification');
    
    // Check if the toast element exists and has the 'data-show="true"' attribute
    if (toast && toast.dataset.show === 'true') {
        
        // Add the 'show' class to make it visible
        toast.classList.add('show');
        
        // Hide the toast after 3 seconds
        setTimeout(() => {
            toast.classList.remove('show');
            // Clean up the URL (optional, good practice)
            window.history.replaceState(null, null, window.location.pathname);
        }, 3000);
    }


    /*
     * 2. "Special Offer" Countdown Timer
     */
    const countdownElement = document.getElementById('countdown-timer');
    
    // Check if the countdown element and target date variable exist
    if (countdownElement && typeof countdownTargetDate !== 'undefined') {
        
        // Get the target date from the <script> tag in index.php
        const targetTime = new Date(countdownTargetDate).getTime();

        // Update the countdown every 1 second
        const countdownInterval = setInterval(() => {
            
            // Get today's date and time
            const now = new Date().getTime();
            
            // Find the distance between now and the target date
            const distance = targetTime - now;
            
            // Time calculations for days, hours, minutes, and seconds
            const days = Math.floor(distance / (1000 * 60 * 60 * 24));
            const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((distance % (1000 * 60)) / 1000);
            
            // Format numbers to have two digits
            const format = (num) => num < 10 ? '0' + num : num;

            // Display the result in the element
            document.getElementById('days').innerText = format(days);
            document.getElementById('hours').innerText = format(hours);
            document.getElementById('minutes').innerText = format(minutes);
            document.getElementById('seconds').innerText = format(seconds);
            
            // If the countdown is finished, write some text
            if (distance < 0) {
                clearInterval(countdownInterval);
                countdownElement.innerHTML = "EXPIRED";
            }
        }, 1000);
    }

});