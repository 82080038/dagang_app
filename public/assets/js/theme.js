;(function(w, d){
  var STORAGE_KEY = 'app_theme';
  var DEFAULT_THEME = 'dark-blue';
  var THEMES = ['dark-blue','light-orange','high-contrast','pastel','brand-indigo'];
  var ACCESSIBILITY_KEY = 'app_accessibility_boost';
  function parseRGB(str){
    if (!str) return null;
    var s=str.trim();
    var m=s.match(/^rgba?\((\d+),\s*(\d+),\s*(\d+)/i);
    if (m) return {r:+m[1],g:+m[2],b:+m[3]};
    var h=s.replace('#','');
    if (h.length===3) h=h.split('').map(function(x){return x+x}).join('');
    if (h.length===6){
      return {r:parseInt(h.slice(0,2),16),g:parseInt(h.slice(2,4),16),b:parseInt(h.slice(4,6),16)};
    }
    return null;
  }
  function luminance(rgb){
    function c(v){v/=255;return v<=0.03928?v/12.92:Math.pow((v+0.055)/1.055,2.4)}
    return 0.2126*c(rgb.r)+0.7152*c(rgb.g)+0.0722*c(rgb.b);
  }
  function contrastRatio(l1,l2){
    var L1=Math.max(l1,l2), L2=Math.min(l1,l2);
    return (L1+0.05)/(L2+0.05);
  }
  function idealTextColor(bg){
    var rgb=parseRGB(bg);
    if(!rgb){
      var fallback = getComputedStyle(document.documentElement).getPropertyValue('--card-bg').trim();
      rgb = parseRGB(fallback)||{r:0,g:0,b:0};
    }
    var lum=luminance(rgb);
    var blackLum=luminance({r:0,g:0,b:0});
    var whiteLum=luminance({r:255,g:255,b:255});
    var crBlack=contrastRatio(lum,blackLum);
    var crWhite=contrastRatio(lum,whiteLum);
    return crWhite>=crBlack ? '#ffffff' : '#111111';
  }
  function adjustModalTitleContrast(root){
    if(!root) return;
    var title=root.querySelector('.modal-title');
    if(!title) return;
    var header = title.parentElement && title.parentElement.classList.contains('modal-header') ? title.parentElement : root.querySelector('.modal-header');
    var bg = '';
    if (header) {
      bg = window.getComputedStyle(header).backgroundColor;
    }
    if (!bg || /rgba\(\s*0,\s*0,\s*0,\s*0\s*\)/i.test(bg) || bg==='transparent') {
      var card=root.querySelector('.modal-content');
      bg = card ? window.getComputedStyle(card).backgroundColor : getComputedStyle(document.documentElement).getPropertyValue('--card-bg').trim();
    }
    var color = idealTextColor(bg);
    try { title.style.setProperty('color', color, 'important'); } catch(e){}
  }
  function updateOpenModalTitles(){
    var modals=document.querySelectorAll('.modal.show');
    modals.forEach(function(m){adjustModalTitleContrast(m)});
  }
  
  function applyTheme(name){
    if (THEMES.indexOf(name) === -1) name = DEFAULT_THEME;
    d.documentElement.setAttribute('data-theme', name);
    try { localStorage.setItem(STORAGE_KEY, name); } catch(e){}
    updateOpenModalTitles();
  }
  
  function currentTheme(){try{
      var saved = localStorage.getItem(STORAGE_KEY);
      if (saved && THEMES.indexOf(saved) > -1) return saved;
    } catch(e){}
    return DEFAULT_THEME;
  }
  
  function initSelector(){
    var dropdown = d.getElementById('themeDropdown');
    var items = d.querySelectorAll('[data-theme-select]');
    if (dropdown) {
      var label = d.getElementById('themeCurrentLabel');
      var swatch = d.getElementById('themeCurrentSwatch');
      var setLabel = function(name){
        if (label) { 
          var map = {
            'dark-blue':'Gelap (Biru)',
            'light-orange':'Terang (Oranye)',
            'high-contrast':'Kontras Tinggi',
            'pastel':'Pastel',
            'brand-indigo':'Brand Indigo'
          };
          label.textContent = map[name] || name;
        }
        try {
          var primary = getComputedStyle(d.documentElement).getPropertyValue('--primary-color').trim();
          if (swatch && primary) {
            swatch.style.backgroundColor = primary;
          }}catch(e){}
      };
      setLabel(currentTheme());
      items.forEach(function(el){
        el.addEventListener('click', function(ev){
          var theme = ev.currentTarget.getAttribute('data-theme-select');
          applyTheme(theme);
          setLabel(theme);
        });
      });
    }
  }
  
  function attachKeyboard(menu){
    if (!menu) return;
    var focusables = menu.querySelectorAll('[data-theme-select]');
    var index = -1;
    menu.addEventListener('keydown', function(e){
      if (!focusables.length) return;
      if (e.key === 'ArrowDown') {
        e.preventDefault();
        index = (index + 1) % focusables.length;
        focusables[index].focus();
      } else if (e.key === 'ArrowUp') {
        e.preventDefault();
        index = (index - 1 + focusables.length) % focusables.length;
        focusables[index].focus();
      } else if (e.key === 'Enter' || e.key === ' ') {
        e.preventDefault();
        var btn = focusables[index >= 0 ? index : 0];
        if (btn) btn.click();
      }
    });
  }
  
  function init(){
    applyTheme(currentTheme());
    initSelector();
    try {
      var ddEl = document.getElementById('themeDropdown');
      var swatch = document.getElementById('themeCurrentSwatch');
      try {
        var primaryInit = getComputedStyle(document.documentElement).getPropertyValue('--primary-color').trim();
        if (swatch && primaryInit) swatch.style.backgroundColor = primaryInit;
      } catch(e){}
      // Accessibility Boost
      var accBtn = document.getElementById('accessibilityToggle');
      var accLabel = document.getElementById('accessibilityLabel');
      var accOn = false;
      try {
        accOn = localStorage.getItem(ACCESSIBILITY_KEY) === '1';
      } catch(e){}
      function applyBoost(on){
        if (on) {
          d.documentElement.classList.add('accessibility-boost');
          accBtn && accBtn.setAttribute('aria-pressed','true');
          accLabel && (accLabel.textContent = 'Aksesibilitas: On');
          try { localStorage.setItem(ACCESSIBILITY_KEY, '1'); } catch(e){}
        } else {
          d.documentElement.classList.remove('accessibility-boost');
          accBtn && accBtn.setAttribute('aria-pressed','false');
          accLabel && (accLabel.textContent = 'Aksesibilitas: Off');
          try { localStorage.setItem(ACCESSIBILITY_KEY, '0'); } catch(e){}
        }
      }
      applyBoost(accOn);
      if (accBtn) {
        accBtn.addEventListener('click', function(){
          accOn = !accOn;
          applyBoost(accOn);
        });
      }
      if (ddEl && window.bootstrap && bootstrap.Dropdown) {
        var dd = new bootstrap.Dropdown(ddEl, { autoClose: true, display: 'static', popperConfig: { strategy: 'fixed' } });
        ddEl.addEventListener('click', function(ev){
          ev.preventDefault();
          ev.stopPropagation();
          dd.toggle();
          var menu = ddEl.nextElementSibling;
          attachKeyboard(menu);
        });
        ddEl.addEventListener('shown.bs.dropdown', function(){
          var menu = ddEl.nextElementSibling;
          attachKeyboard(menu);
          var first = menu && menu.querySelector('[data-theme-select]');
          if (first) first.focus();
        });
      } else if (ddEl) {
        // Fallback toggle if Bootstrap JS not available
        var menu = ddEl.nextElementSibling;
        ddEl.addEventListener('click', function(ev){
          ev.preventDefault();
          ev.stopPropagation();
          if (menu && menu.classList.contains('dropdown-menu')) {
            menu.classList.toggle('show');
            ddEl.setAttribute('aria-expanded', menu.classList.contains('show') ? 'true' : 'false');
          }
          attachKeyboard(menu);
          var first = menu && menu.querySelector('[data-theme-select]');
          if (first) first.focus();
        });
        document.addEventListener('click', function(ev){
          if (!ddEl.parentElement.contains(ev.target) && menu && menu.classList.contains('show')) {
            menu.classList.remove('show');
            ddEl.setAttribute('aria-expanded', 'false');
          }
        });
        document.addEventListener('keydown', function(ev){
          if (ev.key === 'Escape' && menu && menu.classList.contains('show')) {
            menu.classList.remove('show');
            ddEl.setAttribute('aria-expanded', 'false');
          }
        });
      }}catch(e){}
    document.addEventListener('shown.bs.modal', function(e){
      adjustModalTitleContrast(e.target);
      setupAutoFormNav(e.target);
    });
    document.addEventListener('hide.bs.modal', function(e){
      var t=e.target.querySelector('.modal-title'); if(t) t.style.removeProperty('color');
    });
    setupAutoFormNav(document);
  }
  
  if (d.readyState === 'loading') {
    d.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
  
  w.Theme = {
    apply: applyTheme,
    current: currentTheme,
    list: THEMES.slice()
  };
  function setupAutoFormNav(root){
    var forms = (root.querySelectorAll ? root.querySelectorAll('form') : []);
    forms.forEach(function(form){
      ensureAddressTabOrder(form);
      attachPostalCodeAuto(form);
      var elements = Array.prototype.slice.call(form.querySelectorAll('input, select, textarea')).filter(function(el){
        var readonly = el.readOnly || el.getAttribute('readonly') !== null;
        var hidden = el.type === 'hidden' || el.offsetParent === null;
        return !readonly && !hidden;
      });
      // Preserve DOM order for logical navigation across fields
      function nextEl(current){
        var idx = elements.indexOf(current);
        for (var i = idx + 1; i < elements.length; i++){
          var n = elements[i];
          return n;
        }
        return null;
      }
      function openSelect(sel){try{
          sel.focus();
          var m = new MouseEvent('mousedown', {bubbles:true});
          sel.dispatchEvent(m);
          sel.click();
          var k = new KeyboardEvent('keydown', {key:'ArrowDown', bubbles:true});
          sel.dispatchEvent(k);
        } catch(e){}
      }
      function goNext(current){
        if (current && current.willValidate && !current.checkValidity()){try{ current.reportValidity(); } catch(e){}
          return;
        }
        var n = nextEl(current);
        if (!n) return;
        if (n.disabled || n.readOnly){
          var attempts = 0;
          var tm = setInterval(function(){
            attempts++;
            if (!n.disabled && !n.readOnly){
              clearInterval(tm);
              try { n.focus(); if (n.tagName === 'SELECT') openSelect(n); } catch(e){}
            } else if (attempts > 30){
              clearInterval(tm);
            }
          }, 100);
          return;
        }
        try {
          n.focus();
          if (n.tagName === 'SELECT') openSelect(n);
        } catch(e){}
      }
      elements.forEach(function(el){
        if (el.tagName === 'SELECT'){
          el.addEventListener('focus', function(){ openSelect(el); });
          el.addEventListener('change', function(){ goNext(el); });
        } else {
          el.addEventListener('change', function(){ goNext(el); });
          el.addEventListener('keydown', function(e){
            if (e.key === 'Enter' && el.tagName !== 'TEXTAREA'){
              e.preventDefault();
              goNext(el);
            }
          });
        }
      });
    });
  }
  function ensureAddressTabOrder(form){try{
      var province = form.querySelector('#province_id');
      var regency = form.querySelector('#regency_id');
      var district = form.querySelector('#district_id');
      var village = form.querySelector('#village_id');
      var street = form.querySelector('#address_detail') || form.querySelector('[name="street_address"]');
      var base = 1000;
      if (province) province.tabIndex = base + 1;
      if (regency) regency.tabIndex = base + 2;
      if (district) district.tabIndex = base + 3;
      if (village) village.tabIndex = base + 4;
      if (street) street.tabIndex = base + 5;
    } catch(e){}
  }
  
  function attachPostalCodeAuto(form){
    var village = form.querySelector('#village_id');
    var district = form.querySelector('#district_id');
    var display = form.querySelector('#postalCodeDisplay');
    function focusStreet(){
      var street = form.querySelector('#address_detail') || form.querySelector('[name="street_address"]');
      if (street && !street.readOnly) {try{ street.focus(); } catch(e){}
      }
    }
    if (!village || !display) return;
    if (district) {
      district.addEventListener('change', function(){ display.textContent='-'; });
    }
    village.addEventListener('change', function(){
      var v = village.value;
      // Always clear postal code display first when village changes
      display.textContent='-';
      
      if (!v) {
        // If village is empty/null, keep postal code empty and focus on street
        focusStreet();
        return;
      }
      
      var opt = village.querySelector('option[value="'+v+'"]');
      var pc = opt ? opt.getAttribute('data-postal-code') : null;
      
      if (pc) { 
        display.textContent = pc; 
        focusStreet(); 
        return; 
      }
      
      // Fetch from API if no postal code in option data
      try {
        fetch('index.php?page=address&action=get-postal-code&village_id=' + encodeURIComponent(v))
          .then(function(r){ return r.json(); })
          .then(function(res){ 
            if (res && res.status === 'success') { 
              display.textContent = res.data.postal_code || '-'; 
            } else {
              // Keep display as '-' if no postal code found
              display.textContent = '-';
            }
            focusStreet(); 
          })
          .catch(function() {
            // Keep display as '-' on error
            display.textContent = '-';
            focusStreet();
          });
      } catch(e){
        // Keep display as '-' on exception
        display.textContent = '-';
        focusStreet();
      }
    });
  }
})(window, document);
