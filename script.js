/******************************************************
 * TYPEWRITER EFFECT (30ms per character)
 ******************************************************/
document.addEventListener('DOMContentLoaded', () => {
    const textElement = document.getElementById('typewriterText');
    const textString = textElement.textContent.trim();
    let index = 0;
    textElement.textContent = '';
  
    function typeWriter() {
      if (index < textString.length) {
        textElement.textContent += textString.charAt(index);
        index++;
        setTimeout(typeWriter, 30);
      }
    }
    typeWriter();
  });
  
  /******************************************************
   * DEVICE THEME PREFERENCE & THEME TOGGLE
   ******************************************************/
  const themeToggleBtn = document.getElementById('themeToggleBtn');
  // Apply device theme preference on load if user hasn't toggled yet.
  if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
    document.body.classList.add('dark-mode');
    themeToggleBtn.innerHTML = '<i class="fa fa-sun"></i>';
  } else {
    themeToggleBtn.innerHTML = '<i class="fa fa-moon"></i>';
  }
  
  themeToggleBtn.addEventListener('click', () => {
    document.body.classList.toggle('dark-mode');
    if (document.body.classList.contains('dark-mode')) {
      themeToggleBtn.innerHTML = '<i class="fa fa-sun"></i>';
    } else {
      themeToggleBtn.innerHTML = '<i class="fa fa-moon"></i>';
    }
  });
  
  /******************************************************
   * FADE-IN ANIMATION FOR CARDS (INTERSECTION OBSERVER)
   ******************************************************/
  const cardsToAnimate = document.querySelectorAll('.card-to-animate');
  const observerOptions = { threshold: 0.2 };
  const observer = new IntersectionObserver((entries, obs) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.classList.add('in-view');
        obs.unobserve(entry.target);
      }
    });
  }, observerOptions);
  cardsToAnimate.forEach(card => observer.observe(card));
  
  /******************************************************
   * SCROLL BUTTONS: UP & DOWN NAVIGATION WITH DOUBLE-CLICK SKIP
   * (Double-click threshold set to 250ms)
   ******************************************************/
  const sections = Array.from(document.querySelectorAll('section.content-section'));
  const upButton = document.getElementById('upButton');
  const downButton = document.getElementById('downButton');
  
  // Variables to count rapid clicks and timeouts for each button
  let upClickCount = 0;
  let downClickCount = 0;
  const quickClickThreshold = 250; // 250 ms for double-click
  let upTimeout;
  let downTimeout;
  
  // Returns the index of the section that is currently at or just above the scroll position.
  function getCurrentSectionIndex() {
    let scrollPos = window.scrollY;
    let index = -1; // start with -1 to account for the very top of the page
    sections.forEach((section, i) => {
      if (scrollPos >= section.offsetTop - 50) {
        index = i;
      }
    });
    return index;
  }
  
  function scrollToSection(index) {
    if (index >= 0 && index < sections.length) {
      sections[index].scrollIntoView({ behavior: 'smooth' });
    }
  }
  
  // Down Button: Normal behavior scrolls one section down.
  // If double-clicked, scroll to the very bottom.
  downButton.addEventListener('click', () => {
    downClickCount++;
    clearTimeout(downTimeout);
    downTimeout = setTimeout(() => { downClickCount = 0; }, quickClickThreshold);
  
    if (downClickCount >= 2) {
      window.scrollTo({ top: document.body.scrollHeight, behavior: 'smooth' });
      downClickCount = 0;
      return;
    }
    
    let currentIndex = getCurrentSectionIndex();
    let nextIndex = currentIndex + 1;
    if (nextIndex >= sections.length) {
      window.scrollTo({ top: 0, behavior: 'smooth' });
    } else {
      scrollToSection(nextIndex);
    }
  });
  
  // Up Button: Normal behavior scrolls one section up.
  // If double-clicked, scroll to the very top.
  upButton.addEventListener('click', () => {
    upClickCount++;
    clearTimeout(upTimeout);
    upTimeout = setTimeout(() => { upClickCount = 0; }, quickClickThreshold);
  
    if (upClickCount >= 2) {
      window.scrollTo({ top: 0, behavior: 'smooth' });
      upClickCount = 0;
      return;
    }
    
    let currentIndex = getCurrentSectionIndex();
    if (currentIndex <= 0) {
      window.scrollTo({ top: 0, behavior: 'smooth' });
    } else {
      scrollToSection(currentIndex - 1);
    }
  });
  
  /******************************************************
   * CONTACT FORM VALIDATION & SEND MESSAGE
   ******************************************************/
  const msgName = document.getElementById('msgName');
  const msgEmail = document.getElementById('msgEmail');
  const msgTel = document.getElementById('msgTel');
  const msgText = document.getElementById('msgText');
  const sendButton = document.getElementById('sendButton');
  
  function validateForm() {
    // At least one of name, email, or tel must be non-empty.
    const anyFilled = msgName.value.trim() !== "" || msgEmail.value.trim() !== "" || msgTel.value.trim() !== "";
    // Text area must have at least 10 characters.
    const messageValid = msgText.value.trim().length >= 10;
    
    sendButton.disabled = !(anyFilled && messageValid);
    
    // Optionally, you can change button appearance if enabled.
    if (!sendButton.disabled) {
      sendButton.style.backgroundColor = "var(--secondary-color)";
    } else {
      sendButton.style.backgroundColor = "#cccccc";
    }
  }
  
  // Listen for input events on form fields.
  [msgName, msgEmail, msgTel, msgText].forEach(input => {
    input.addEventListener('input', validateForm);
  });
  
  // When send message is pressed, send the data via POST.
  // Note: This requires a server-side script (for example, messages/save.php) to actually save the message.
  sendButton.addEventListener('click', (e) => {
    e.preventDefault();
    const formData = {
      name: msgName.value.trim(),
      email: msgEmail.value.trim(),
      tel: msgTel.value.trim(),
      message: msgText.value.trim()
    };
  
    // Example POST request using fetch. Adjust the URL to your server-side script.
    fetch('messages/save.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(formData)
    })
    .then(response => {
      if (response.ok) {
        alert("Message sent successfully!");
        // Optionally, clear the form:
        msgName.value = "";
        msgEmail.value = "";
        msgTel.value = "";
        msgText.value = "";
        validateForm();
      } else {
        alert("There was an error sending your message.");
      }
    })
    .catch(error => {
      console.error("Error:", error);
      alert("There was an error sending your message.");
    });
  });
  