# Premium UI Implementation Guide

## Quick Start - Activate Premium Coffee Theme

Your barista academy now has a **premium coffee-themed design system**. Here's how to fully activate it:

---

## Step 1: CSS Variables Are Live ✅

The color palette is already defined in your CSS:

```css
/* Primary Coffee Colors */
--color-primary: #6F4E37         /* Deep coffee brown */
--color-accent: #C67C4E          /* Warm copper accent */
--color-accent-gold: #D4A574     /* Premium gold */
--color-bg: #F8F7F4              /* Cream background */

/* Use in styles */
button { background: var(--color-accent); }
.hero { background: linear-gradient(to right, 
    var(--color-primary), 
    var(--color-accent-gold)); }
```

---

## Step 2: Update Key HTML Classes

Replace the Bootstrap classes with custom ones in your templates:

### Navigation Bar
```html
<!-- OLD -->
<nav class="navbar navbar-light">

<!-- NEW -->
<nav class="navbar navbar-premium" style="
    background-color: var(--color-surface);
    border-bottom: 1px solid var(--color-border);
    box-shadow: var(--shadow-sm);
">
```

### Hero Section
```html
<!-- NEW HERO WITH COFFEE GRADIENT -->
<section class="hero-section" style="
    background: linear-gradient(135deg, 
        var(--color-primary) 0%, 
        var(--color-accent-gold) 100%);
    color: white;
    padding: 5rem 2rem;
    min-height: 80vh;
">
    <h1 style="
        font-size: 3.5rem;
        font-weight: 800;
        margin-bottom: 1.5rem;
    ">
        Master the Art of 
        <span style="color: var(--color-accent-gold);">Specialty Coffee</span>
    </h1>
</section>
```

### Course Cards
```html
<!-- PREMIUM COURSE CARD -->
<div class="course-card" style="
    background: var(--color-surface);
    border-radius: 1rem;
    box-shadow: var(--shadow-md);
    transition: all 200ms ease;
    border: 1px solid var(--color-border);
">
    <img src="thumbnail.jpg" style="
        height: 200px;
        object-fit: cover;
        border-radius: 1rem 1rem 0 0;
        background: linear-gradient(135deg, 
            var(--color-accent) 0%, 
            var(--color-accent-gold) 100%);
    ">
    
    <div style="padding: 1.5rem;">
        <span style="
            color: var(--color-accent);
            font-weight: 700;
            font-size: 0.875rem;
            text-transform: uppercase;
        ">Barista Skills</span>
        
        <h5 style="
            color: var(--color-primary);
            margin: 0.5rem 0;
        ">Espresso Mastery Course</h5>
    </div>
</div>

<!-- HOVER EFFECT -->
<style>
    .course-card:hover {
        transform: translateY(-8px);
        box-shadow: var(--shadow-lg);
        border-color: var(--color-accent);
    }
</style>
```

---

## Step 3: Buttons - Premium Style

### Primary Button (Warm Gradient)
```html
<button style="
    background: linear-gradient(135deg, 
        var(--color-accent) 0%, 
        var(--color-accent-gold) 100%);
    color: white;
    padding: 1rem 2rem;
    border: none;
    border-radius: 0.75rem;
    font-weight: 700;
    cursor: pointer;
    box-shadow: var(--shadow-md);
    transition: all 200ms ease;
">
    Enroll Now
</button>

<style>
    button:hover {
        transform: translateY(-3px);
        box-shadow: var(--shadow-lg);
    }
</style>
```

### Secondary Button (Outline)
```html
<button style="
    background: transparent;
    color: var(--color-primary);
    padding: 1rem 2rem;
    border: 2px solid var(--color-primary);
    border-radius: 0.75rem;
    font-weight: 700;
    cursor: pointer;
    transition: all 200ms ease;
">
    Learn More
</button>

<style>
    button:hover {
        background: var(--color-primary);
        color: white;
    }
</style>
```

---

## Step 4: Form Inputs

```html
<input type="text" placeholder="Search courses..." style="
    padding: 0.75rem 1rem;
    border: 1px solid var(--color-border);
    border-radius: 0.5rem;
    font-size: 1rem;
    transition: all 200ms ease;
">

<style>
    input:focus {
        outline: none;
        border-color: var(--color-accent);
        box-shadow: 0 0 0 3px rgba(198, 124, 78, 0.1);
    }
</style>
```

---

## Step 5: Dashboard Cards

```html
<!-- STAT CARD -->
<div class="stat-card" style="
    background: var(--color-surface);
    border-radius: 1rem;
    padding: 1.5rem;
    border: 1px solid var(--color-border);
    transition: all 200ms ease;
">
    <div style="
        width: 56px;
        height: 56px;
        background: linear-gradient(135deg, 
            var(--color-accent) 0%, 
            var(--color-accent-gold) 100%);
        border-radius: 0.75rem;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.75rem;
        margin-bottom: 1rem;
    ">
        <i class="bi bi-book-half"></i>
    </div>
    
    <h4 style="
        color: var(--color-primary);
        font-size: 1.5rem;
        margin-bottom: 0.5rem;
    ">12</h4>
    
    <small style="color: var(--color-text-muted);">
        Courses Enrolled
    </small>
</div>

<style>
    .stat-card:hover {
        box-shadow: var(--shadow-lg);
        border-color: var(--color-accent);
    }
</style>
```

---

## Step 6: Categories Grid

```html
<!-- CATEGORY CARD GRID -->
<div style="
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 2rem;
    margin: 3rem 0;
">
    <?php foreach ($categories as $cat): ?>
    <a href="#" style="
        text-decoration: none;
        color: inherit;
    ">
        <div style="
            background: var(--color-surface);
            border-radius: 1rem;
            padding: 2rem;
            border: 2px solid var(--color-border);
            transition: all 200ms ease;
            cursor: pointer;
        ">
            <div style="
                width: 56px;
                height: 56px;
                background: linear-gradient(135deg, 
                    var(--color-accent) 0%, 
                    var(--color-accent-gold) 100%);
                border-radius: 0.75rem;
                display: flex;
                align-items: center;
                justify-content: center;
                color: white;
                font-size: 1.75rem;
                margin-bottom: 1rem;
            ">
                <i class="bi <?= e($cat['icon']) ?>"></i>
            </div>
            
            <h5 style="
                color: var(--color-primary);
                margin-bottom: 0.5rem;
            ">
                <?= e($cat['name']) ?>
            </h5>
            
            <p style="
                color: var(--color-text-muted);
                margin: 0;
                font-size: 0.95rem;
            ">
                <?= e($cat['description']) ?>
            </p>
        </div>
    </a>
    <?php endforeach; ?>
</div>

<style>
    /* Hover animation */
    [role="link"]:hover > div {
        border-color: var(--color-accent);
        box-shadow: var(--shadow-lg);
        transform: translateY(-5px);
    }
</style>
```

---

## Step 7: Hero Section with Featured Card

```html
<section style="
    background: linear-gradient(135deg, 
        var(--color-primary) 0%, 
        #5a3d2a 100%);
    color: white;
    padding: 5rem 2rem;
">
    <div class="container" style="
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 3rem;
        align-items: center;
    ">
        <!-- Left Content -->
        <div>
            <div style="
                display: inline-flex;
                align-items: center;
                gap: 0.5rem;
                background: rgba(212, 165, 116, 0.2);
                padding: 0.75rem 1.25rem;
                border-radius: 9999px;
                margin-bottom: 1.5rem;
                color: var(--color-accent-gold);
                font-weight: 600;
                font-size: 0.875rem;
            ">
                <i class="bi bi-star-fill"></i>
                Rwanda's Premier Barista Academy
            </div>
            
            <h1 style="
                font-size: 3.5rem;
                font-weight: 800;
                margin-bottom: 1.5rem;
                line-height: 1.1;
            ">
                Master the Art of <span style="color: var(--color-accent-gold);">Specialty Coffee</span>
            </h1>
            
            <p style="
                font-size: 1.25rem;
                opacity: 0.9;
                margin-bottom: 2rem;
                line-height: 1.8;
            ">
                Learn from SCA-certified roasters and award-winning baristas. 
                Gain internationally recognized credentials.
            </p>
            
            <div style="
                display: flex;
                gap: 1.5rem;
                flex-wrap: wrap;
            ">
                <button style="
                    padding: 1rem 2rem;
                    background: linear-gradient(135deg, 
                        var(--color-accent) 0%, 
                        var(--color-accent-gold) 100%);
                    color: white;
                    border: none;
                    border-radius: 0.75rem;
                    font-weight: 700;
                    cursor: pointer;
                    box-shadow: var(--shadow-lg);
                    display: inline-flex;
                    align-items: center;
                    gap: 0.5rem;
                ">
                    <i class="bi bi-play-circle-fill"></i>
                    Explore Courses
                </button>
                
                <button style="
                    padding: 1rem 2rem;
                    background: transparent;
                    color: white;
                    border: 2px solid white;
                    border-radius: 0.75rem;
                    font-weight: 700;
                    cursor: pointer;
                ">
                    Get Started
                </button>
            </div>
        </div>
        
        <!-- Right Featured Card -->
        <div style="
            background: rgba(0, 0, 0, 0.3);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(212, 165, 116, 0.3);
            border-radius: 1rem;
            padding: 2rem;
            color: white;
        ">
            <span style="
                display: inline-block;
                background: var(--warning);
                color: var(--color-primary);
                padding: 0.5rem 1rem;
                border-radius: 0.5rem;
                font-weight: 700;
                font-size: 0.875rem;
                margin-bottom: 1rem;
            ">FEATURED MASTERCLASS</span>
            
            <h4 style="font-size: 1.5rem; margin-bottom: 1rem;">
                Foundation Barista Skills
            </h4>
            
            <p style="opacity: 0.9; margin-bottom: 1.5rem;">
                Master grind calibration, extraction ratios, and latte art.
            </p>
            
            <div style="
                background: rgba(255, 255, 255, 0.1);
                height: 6px;
                border-radius: 9999px;
                overflow: hidden;
                margin-bottom: 1.5rem;
            ">
                <div style="
                    background: linear-gradient(to right, 
                        var(--color-accent), 
                        var(--color-accent-gold));
                    height: 100%;
                    width: 100%;
                    border-radius: 9999px;
                "></div>
            </div>
            
            <div style="
                display: flex;
                justify-content: space-between;
                align-items: center;
            ">
                <div>
                    <small style="opacity: 0.8;">Tuition</small>
                    <span style="
                        font-size: 1.75rem;
                        font-weight: 800;
                        color: var(--color-success);
                    ">FREE</span>
                </div>
                <button style="
                    padding: 0.75rem 1.5rem;
                    background: var(--color-accent);
                    color: white;
                    border: none;
                    border-radius: 0.75rem;
                    font-weight: 700;
                    cursor: pointer;
                ">
                    Start Now
                </button>
            </div>
        </div>
    </div>
</section>

@media (max-width: 768px) {
    .container {
        grid-template-columns: 1fr !important;
    }
}
```

---

## Step 8: Quiz Interface

```html
<!-- QUIZ HEADER -->
<div style="
    background: linear-gradient(135deg, 
        var(--color-primary) 0%, 
        var(--color-accent) 100%);
    color: white;
    padding: 1.5rem;
    border-radius: 1rem;
    margin-bottom: 2rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
">
    <div>
        <span style="
            display: inline-block;
            background: var(--warning);
            color: var(--color-primary);
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            font-weight: 700;
            font-size: 0.875rem;
            margin-bottom: 0.5rem;
        ">Pass Requirement: 75%</span>
        
        <h3 style="
            color: white;
            font-weight: 800;
            margin-bottom: 0;
        ">Barista Certification Exam</h3>
    </div>
    
    <div style="
        background: white;
        color: var(--color-primary);
        padding: 0.75rem 1.5rem;
        border-radius: 0.75rem;
        text-align: center;
    ">
        <small style="
            display: block;
            font-size: 0.75rem;
            opacity: 0.7;
        ">TIME REMAINING</small>
        <span style="
            font-size: 1.5rem;
            font-weight: 800;
            font-family: monospace;
        ">20:00</span>
    </div>
</div>

<!-- QUESTION CARD -->
<div style="
    background: var(--color-surface);
    padding: 2rem;
    border-radius: 1rem;
    box-shadow: var(--shadow-md);
    border: 1px solid var(--color-border);
    margin-bottom: 2rem;
">
    <div style="
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
    ">
        <span style="
            display: inline-block;
            background: var(--color-bg);
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            border: 1px solid var(--color-border);
            font-size: 0.875rem;
        ">Question 1 of 4</span>
        <span style="color: var(--color-accent); font-weight: 700;">25 Points</span>
    </div>
    
    <h5 style="
        color: var(--color-primary);
        font-size: 1.25rem;
        margin-bottom: 2rem;
    ">What is the standard brew ratio for espresso extraction?</h5>
    
    <div style="display: flex; flex-direction: column; gap: 1rem;">
        <!-- Option -->
        <label style="
            padding: 1rem;
            border: 2px solid var(--color-border);
            border-radius: 0.75rem;
            cursor: pointer;
            transition: all 200ms ease;
            display: flex;
            align-items: center;
            gap: 1rem;
        ">
            <input type="radio" name="answer" style="
                width: 20px;
                height: 20px;
                cursor: pointer;
            ">
            <span>1:2 (18g coffee in, 36g espresso out)</span>
        </label>
        
        <label style="
            padding: 1rem;
            border: 2px solid var(--color-border);
            border-radius: 0.75rem;
            cursor: pointer;
            transition: all 200ms ease;
            display: flex;
            align-items: center;
            gap: 1rem;
        ">
            <input type="radio" name="answer" style="
                width: 20px;
                height: 20px;
                cursor: pointer;
            ">
            <span>1:1 (18g coffee in, 18g espresso out)</span>
        </label>
    </div>
</div>

<style>
    label:hover {
        border-color: var(--color-accent);
        background: var(--color-bg);
    }
</style>
```

---

## Step 9: Dark Mode Toggle

Add this JavaScript for theme switching:

```javascript
// Theme Toggle
const themeToggleBtn = document.getElementById('themeToggleBtn');
const htmlElement = document.documentElement;

// Check saved preference
const savedTheme = localStorage.getItem('theme') || 'light';
htmlElement.setAttribute('data-bs-theme', savedTheme);

themeToggleBtn.addEventListener('click', () => {
    const currentTheme = htmlElement.getAttribute('data-bs-theme');
    const newTheme = currentTheme === 'light' ? 'dark' : 'light';
    
    htmlElement.setAttribute('data-bs-theme', newTheme);
    localStorage.setItem('theme', newTheme);
    
    // Update icon
    const icon = themeToggleBtn.querySelector('i');
    icon.className = newTheme === 'light' 
        ? 'bi bi-moon-stars-fill' 
        : 'bi bi-sun-fill';
});
```

---

## File Locations Reference

- **Color definitions:** `/public/assets/css/app.css` (CSS variables)
- **Bootstrap:** CDN (no local file)
- **Icons:** Bootstrap Icons (CDN)
- **Fonts:** Google Fonts (CDN)

---

## Color Quick Reference

```
Coffee Brown:    #6F4E37
Coffee Dark:     #2C1810
Cream/Gold:      #D4A574
Warm Accent:     #C67C4E
Success Green:   #52B788
Warning Yellow:  #FFB703
Background:      #F8F7F4
Surface White:   #FFFFFF
Text Dark:       #2C1810
Muted Gray:      #6B6B6B
Border Light:    #E8E6E1
```

---

## Testing the Design

1. **Homepage:** Check hero gradient and featured card
2. **Courses Page:** Verify card hover effects
3. **Dashboard:** Check stat cards and layout
4. **Forms:** Test input focus states
5. **Dark Mode:** Toggle and verify colors
6. **Mobile:** Test responsive layout

---

## Common CSS Updates to Apply

### Page Backgrounds
```css
body { background-color: var(--color-bg); }
```

### Text Colors
```css
h1, h2, h3, h4, h5, h6 { color: var(--color-primary); }
p { color: var(--color-text-main); }
.muted { color: var(--color-text-muted); }
```

### Buttons
```css
.btn-primary {
    background: linear-gradient(135deg, 
        var(--color-accent) 0%, 
        var(--color-accent-gold) 100%);
}
.btn-primary:hover {
    transform: translateY(-3px);
    box-shadow: var(--shadow-lg);
}
```

---

## Next Steps

1. ✅ **Colors defined** - CSS variables set
2. 🔄 **Update templates** - Apply HTML/CSS examples above
3. 🎨 **Polish components** - Refine buttons, forms, cards
4. 📱 **Responsive test** - Test on mobile devices
5. 🌙 **Dark mode verify** - Check all colors in dark mode

---

**Your premium barista academy is ready to shine!** ☕✨
