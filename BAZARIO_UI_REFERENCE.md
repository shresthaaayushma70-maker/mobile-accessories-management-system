# BAZARIO - UI/UX REFERENCE GUIDE

## Table of Contents
1. [Navigation Structure](#navigation-structure)
2. [Dashboard Layouts](#dashboard-layouts)
3. [Order Tracking UI](#order-tracking-ui)
4. [Notification System](#notification-system)
5. [Color & Typography](#color--typography)
6. [Component Specifications](#component-specifications)
7. [Responsive Breakpoints](#responsive-breakpoints)

---

## Navigation Structure

### Main Navigation Bar
```
┌─────────────────────────────────────────────────────────────────────┐
│  🛍️ BAZARIO      Home    Products    My Orders    Profile    🔔 (3) │
└─────────────────────────────────────────────────────────────────────┘
  Navy Blue Background (#001a33) | White Text | Fixed Position
```

### Sidebar (User/Admin Dashboard)
```
┌──────────────────┐
│ 🛍️ BAZARIO      │  Height: 100vh, Width: 250px
├──────────────────┤  Position: Fixed, Left: 0
│ 📊 Dashboard     │  Background: Navy Blue (#001a33)
│ 📦 Orders        │
│ 🔔 Notifications │
│ ⚙️ Settings      │
│ 👤 Profile       │
│ 🚪 Logout        │
└──────────────────┘
Active Item: Left border highlight + lighter background
```

---

## Dashboard Layouts

### User Dashboard (After Login)
```
┌─────────────────────────────────────────────────────────────────────┐
│                       Welcome, John! 👋                             │
└─────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────┐
│                    QUICK STATS                                      │
├──────────────────┬──────────────────┬──────────────────┬────────────┤
│ 📦 Total Orders  │ 🚚 In Transit    │ ✅ Delivered      │ 💰 Spent  │
│       5          │       2          │       3          │   ₹5,999   │
└──────────────────┴──────────────────┴──────────────────┴────────────┘

┌─────────────────────────────────────────────────────────────────────┐
│                    RECENT ORDERS                                    │
├──────────────┬──────────────┬──────────────┬──────────────┬─────────┤
│ Order #      │ Date         │ Total        │ Status       │ Action  │
├──────────────┼──────────────┼──────────────┼──────────────┼─────────┤
│ ORD123456    │ Jan 10, 2026 │ ₹2,999       │ Delivered ✓  │ Track → │
│ ORD123455    │ Jan 08, 2026 │ ₹1,500       │ Packing 📦   │ Track → │
│ ORD123454    │ Jan 05, 2026 │ ₹3,500       │ Delivered ✓  │ Track → │
└──────────────┴──────────────┴──────────────┴──────────────┴─────────┘

┌─────────────────────────────────────────────────────────────────────┐
│                    FEATURED PRODUCTS                                │
├─────────────────────┬─────────────────────┬─────────────────────┐
│   Product Card 1    │   Product Card 2    │   Product Card 3    │
│   [Image]           │   [Image]           │   [Image]           │
│   Name              │   Name              │   Name              │
│   Category          │   Category          │   Category          │
│   ₹999              │   ₹1,299            │   ₹499              │
│   ⭐⭐⭐⭐⭐         │   ⭐⭐⭐⭐⭐         │   ⭐⭐⭐⭐⭐         │
│   [Order] [Details] │   [Order] [Details] │   [Order] [Details] │
└─────────────────────┴─────────────────────┴─────────────────────┘
```

### Admin Dashboard (Orders Management)
```
┌─────────────────────────────────────────────────────────────────────┐
│                   ADMIN DASHBOARD                                   │
└─────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────┐
│                    SYSTEM OVERVIEW                                  │
├──────────────────┬──────────────────┬──────────────────┬────────────┤
│ Total Orders     │ Pending          │ In Transit       │ Delivered  │
│      23          │       5          │       8          │     10     │
└──────────────────┴──────────────────┴──────────────────┴────────────┘

┌─────────────────────────────────────────────────────────────────────┐
│                    ALL ORDERS                                       │
├──────┬──────────────┬──────────┬──────────────┬──────────────┬──────┤
│ ID   │ Customer     │ Amount   │ Status       │ Date         │ Action│
├──────┼──────────────┼──────────┼──────────────┼──────────────┼──────┤
│ 123  │ John Doe     │ ₹2,999   │ Processing ▶ │ Jan 10, 2026 │ [✎][⋮]│
│ 122  │ Jane Smith   │ ₹1,500   │ Packing     │ Jan 08, 2026 │ [✎][⋮]│
│ 121  │ Mike Johnson │ ₹3,500   │ Delivered ✓  │ Jan 05, 2026 │ [✎][⋮]│
└──────┴──────────────┴──────────┴──────────────┴──────────────┴──────┘

[Action Status Update Dropdown]
```

---

## Order Tracking UI

### Order Tracking Timeline (Full Page)

```
┌─────────────────────────────────────────────────────────────────────┐
│         Order Tracking - Order #ORD123456                  [DELIVERED]│
│         Order Placed: Jan 10, 2026                                  │
└─────────────────────────────────────────────────────────────────────┘

                    ORDER JOURNEY - TIMELINE
                    
     [✓]              [✓]              [✓]              [✓]
   ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
                                                        [✓]
  
  [✓] Order Placed          [✓] Confirmed             [✓] Processing
  Jan 10, 2pm               Jan 10, 3:15pm            Jan 10, 5pm
  
  [✓] Packing               [✓] Out for Delivery      [✓] Delivered
  Jan 11, 10am              Jan 12, 9am               Jan 13, 5:30pm

                    DELIVERY STATUS
  ┌──────────────────────────────────────────┐
  │ Days Taken: 3        Est. Delivery: 13   │
  │ Address: 123 Street, City, State 12345   │
  │ Tracking: TRK987654321                   │
  └──────────────────────────────────────────┘

                    ORDER ITEMS
  ┌──────────────────────────────────────────┐
  │ iPhone 15 Pro      x2      ₹2,000        │
  │ USB-C Charger      x1      ₹999          │
  │ Screen Protector   x1      ₹299          │
  ├──────────────────────────────────────────┤
  │ Total:                     ₹2,999        │
  └──────────────────────────────────────────┘
```

### Single Timeline Step
```
┌─────────────────────────────────────────────────────────────┐
│  [●] Timeline Marker (80x80 circle)                         │
│  ├─ Icon: status-specific                                   │
│  ├─ Background: status-specific color                       │
│  └─ Border: white, shadow                                   │
│                                                              │
│  ├─ Title: "Order Placed" (Bold 16px)                       │
│  ├─ Time: "Jan 10, 2026 at 2:30 PM" (13px gray)           │
│  └─ Description: Status description text                    │
│                                                              │
│  Animation: Active step pulses continuously               │
└─────────────────────────────────────────────────────────────┘

Status-Specific Colors:
• Order Placed     → Blue (#3498db)
• Confirmed        → Blue (#3498db)
• Processing       → Blue (#3498db)
• Packing          → Orange (#e67e22)
• Out for Delivery → Orange (#e67e22)
• Delivered        → Green (#27ae60)
• Cancelled        → Red (#e74c3c)
```

---

## Notification System

### Notification Center dropdown
```
┌────────────────────────────────────┐
│   🔔 NOTIFICATIONS                 │
│   [Mark all as read]               │
├────────────────────────────────────┤
│                                    │
│  [✓] Order Delivered               │
│  ─  Your order has been delivered! │
│  ─  Today at 5:30 PM               │
│  ─                          [→]    │
│                                    │
│  [●] Out for Delivery              │
│  ─  Order is out for delivery.     │
│  ─  Today at 9:00 AM               │
│  ─                          [→]    │
│                                    │
│  [ ] Processing                    │
│  ─  Order is being processed.      │
│  ─  Jan 10 at 10:30 AM             │
│  ─                          [→]    │
│                                    │
├────────────────────────────────────┤
│  Manage Preferences                │
└────────────────────────────────────┘

Legend:
[✓] = Read notification
[●] = Unread notification
[ ] = Old notification
```

### Toast Notification (Bottom Right)
```
┌──────────────────────────────────┐
│  ✓ Your order has been confirmed! │
└──────────────────────────────────┘

Duration: 4 seconds
Position: Bottom Right (20px from edge)
Color: Green (#27ae60) for success
Animation: Slide in from right, fade out
```

### Notification Center Page (Full)
```
┌─────────────────────────────────────────────────────────────┐
│                   NOTIFICATIONS                             │
└─────────────────────────────────────────────────────────────┘

  [Notifications] [Preferences]
  
┌─────────────────────────────────────────────────────────────┐
│ Recent Notifications       [Mark All as Read]              │
├─────────────────────────────────────────────────────────────┤
│                                                              │
│ [✓] ━━━━━━━━━━━━━━━━━ Delivered ━━━━━━━━━━━━━━━━━━ [✓] [→]  │
│      Order #ORD123 has been delivered!                    │
│      Today at 5:30 PM                                     │
│                                                              │
│ [●] ━━━━━━━━━━━━━━━━━ Out for Delivery ━━━━━━━━━━ [→]    │
│      Order is on its way to you!                          │
│      Today at 9:00 AM                                     │
│                                                              │
│ [ ] ━━━━━━━━━━━━━━━━━ Processing ━━━━━━━━━━━━━━━━ [→]    │
│      Order is being processed.                            │
│      Jan 10 at 10:30 AM                                   │
│                                                              │
└─────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────┐
│ Preferences                                                  │
├─────────────────────────────────────────────────────────────┤
│                                                              │
│ Email Notifications:                                        │
│  ☑ Order Placed                      [Toggle Switch]       │
│  ☑ Order Confirmed                   [Toggle Switch]       │
│  ☑ Processing                        [Toggle Switch]       │
│  ☑ Out for Delivery                  [Toggle Switch]       │
│  ☑ Delivered                         [Toggle Switch]       │
│                                                              │
│ SMS Notifications (Optional):                              │
│  Phone: [______________]                                   │
│  ☐ Order Placed                      [Toggle Switch]       │
│  ☒ Out for Delivery                  [Toggle Switch]       │
│  ☒ Delivered                         [Toggle Switch]       │
│                                                              │
│                              [Save Preferences]             │
│                                                              │
└─────────────────────────────────────────────────────────────┘
```

---

## Color & Typography

### Color Palette

```
PRIMARY COLORS:
┌─────────────────────────────────────┐
│ Navy Blue (Primary):  #001a33       │
│ ████████████████████████████████████│
└─────────────────────────────────────┘

┌─────────────────────────────────────┐
│ Navy Accent:  #003366               │
│ ████████████████████████████████████│
└─────────────────────────────────────┘

┌─────────────────────────────────────┐
│ White (Background):  #ffffff        │
│ ░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░│
└─────────────────────────────────────┘

┌─────────────────────────────────────┐
│ Light Gray:  #f5f5f5                │
│ ░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░│
└─────────────────────────────────────┘

SEMANTIC COLORS:
┌─────────────────────────────────────┐
│ Success Green:  #27ae60             │
│ ████████████████████████████████████│
└─────────────────────────────────────┘

┌─────────────────────────────────────┐
│ Processing Blue:  #3498db           │
│ ████████████████████████████████████│
└─────────────────────────────────────┘

┌─────────────────────────────────────┐
│ Warning Orange:  #e67e22            │
│ ████████████████████████████████████│
└─────────────────────────────────────┘

┌─────────────────────────────────────┐
│ Alert Red:  #e74c3c                 │
│ ████████████████████████████████████│
└─────────────────────────────────────┘

TEXT COLORS:
├─ Primary Text:    #333333 (Dark)
├─ Secondary Text:  #666666 (Gray)
├─ Light Text:      #999999 (Very Gray)
└─ Inverse:         #ffffff (White)
```

### Typography Scale

```
H1 (Page Headers):        32px | Bold   | Color: #001a33
H2 (Section Headers):     20px | Bold   | Color: #001a33
H3 (Subsection Headers):  16px | 600    | Color: #001a33
H4 (Card Headers):        14px | 600    | Color: #001a33
Body Text:                14px | 400    | Color: #333333
Small Text:               12px | 400    | Color: #666666
Button Text:              14px | 600    | Color: #ffffff

Font Family:
Primary:   'Segoe UI', Tahoma, Geneva, Verdana, sans-serif
Fallback:  Arial, sans-serif
```

---

## Component Specifications

### Button Styles

```
PRIMARY BUTTON (Navy Blue):
┌─────────────────────┐
│   Order Now         │
└─────────────────────┘
Background:    #001a33
Text:          #ffffff
Padding:       10px 20px
Border-radius: 4px
Hover:         #003366 + shadow
Active:        Darker shade

SECONDARY BUTTON (Gray):
┌─────────────────────┐
│   View Details      │
└─────────────────────┘
Background:    #666666
Text:          #ffffff
Hover:         #333333

DANGER BUTTON (Red):
┌─────────────────────┐
│   Cancel Order      │
└─────────────────────┘
Background:    #e74c3c
Text:          #ffffff
Hover:         #c0392b
```

### Card Styling

```
┌─────────────────────────────────────────┐
│ CARD TITLE                              │
├─────────────────────────────────────────┤
│                                         │
│ Card content goes here                  │
│                                         │
│                                         │
├─────────────────────────────────────────┤
│ Card Footer (optional)                  │
└─────────────────────────────────────────┘

Background:      #ffffff
Border:          1px solid #e0e0e0
Border-radius:   8px
Box-shadow:      0 2px 8px rgba(0,0,0,0.1)
Padding:         20px
Margin-bottom:   20px
Hover shadow:    0 4px 12px rgba(0,0,0,0.15)
```

### Form Elements

```
INPUT FIELD:
┌─────────────────────┐
│ Label              │
│ [User input here] │
└─────────────────────┘

Focus State:
Border:    #001a33 | 2px
Box-shadow: 0 0 0 3px rgba(0,26,51,0.1)

SELECT DROPDOWN:
┌─────────────────────────────────────┐
│ Select an option              ▼    │
└─────────────────────────────────────┘

CHECKBOX:
☑ Option 1
☐ Option 2

TOGGLE SWITCH:
  ○─────  (Off)
  ──────● (On)
```

---

## Responsive Breakpoints

### Desktop (1200px+)
```
Sidebar:      Fixed, 250px
Main Content: Margin-left: 250px
Layout:       Multi-column grid
Cards:        3-4 columns per row
Font:         Full size
```

### Tablet (768px - 1199px)
```
Sidebar:      Collapsible, hamburger menu
Main Content: Full width with padding
Layout:       2-column grid
Cards:        2 columns per row
Font:         Slightly reduced
```

### Mobile (480px - 767px)
```
Sidebar:      Hidden by default, slide-in drawer
Main Content: Full width
Layout:       Single column
Cards:        1 column (stacked)
Font:         Optimized for mobile
Product Grid: Flex layout with smaller images
```

### Small Mobile (<480px)
```
Navigation:   Hamburger menu only
Typography:  18px minimum for clickable text
Buttons:     Full width
Modals:      No top margin, full viewport
Images:      Responsive scaling
```

---

## Accessibility & UX

### Focus States
```
All interactive elements have visible focus:
┌─────────────────────┐
│  [Focused Button]   │
└─────────────────────┘
Outline: 2px solid #001a33
Outline-offset: 2px
```

### Loading States
```
[Loading spinner animation]
  ⟲ Processing your order...

Spinner:  fas fa-spinner (spinning)
Duration: Continuous until complete
Color:    #001a33
```

### Error/Validation
```
┌─────────────────────────────┐
│ ✗ Required field            │
│ [Input field with red border]
└─────────────────────────────┘
Border:     2px solid #e74c3c
Background: rgba(231,76,60,0.05)
Message:    12px red text
```

---

## Animation & Transitions

```
Standard Transition Duration:  0.3s
Easing:                        ease (default)

FADE IN:
From: opacity 0
To:   opacity 1
Duration: 0.5s

SLIDE IN (Left):
From: translateX(-100px), opacity 0
To:   translateX(0), opacity 1
Duration: 0.5s

PULSE (Active Timeline):
0%:   box-shadow 0 0 0 0 rgba(52,152,219,0.7)
50%:  box-shadow 0 0 0 10px rgba(52,152,219,0)
100%: box-shadow 0 0 0 0 rgba(52,152,219,0)
Duration: 2s (infinite)
```

---

## Print Styles

When printing order receipts or confirmations:
```html
@media print {
  .navbar,
  .sidebar,
  .no-print { display: none; }
  
  Body {
    background: white;
    margin: 0;
  }
  
  Cards {
    box-shadow: none;
    border: 1px solid #ddd;
  }
}
```

---

**Design System Version:** 1.0  
**Last Updated:** January 2026  
**Designed For:** Bazario E-Commerce Platform

