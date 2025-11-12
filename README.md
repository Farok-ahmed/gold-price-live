# Gold Price Live - WordPress Plugin

A professional WordPress plugin to display live gold, silver, and platinum prices with an interactive calculator.

## Features

- **Live Price Updates**: Fetches data twice daily from goldprice.org API
- **Five Shortcodes**: Topbar, spot price table, premium jewellery table, comprehensive jewellery table, and interactive calculator
- **Scrap Metal Calculator**: Real-time price calculation with nice select dropdowns
- **AJAX Powered**: Instant calculations without page reload
- **Responsive Design**: Works perfectly on all devices
- **Caching System**: Reduces API calls with 12-hour cache
- **Professional Structure**: Object-oriented code with separate files

## File Structure

```
gold-price-lived/
├── gold-price-lived.php          # Main plugin file
├── assets/
│   ├── css/
│   │   ├── topbar.css            # Topbar styles
│   │   ├── price-table.css       # Spot price table styles
│   │   ├── premium-jewellery.css # Premium jewellery table styles
│   │   ├── jewellery.css         # Comprehensive jewellery table styles
│   │   └── calculator.css        # Calculator styles
│   └── js/
│       └── calculator.js         # Calculator JavaScript
└── includes/
    ├── class-topbar.php          # Topbar shortcode class
    ├── class-price-table.php     # Spot price table shortcode class
    ├── class-premium-jewellery.php # Premium jewellery table shortcode class
    ├── class-jewellery.php       # Comprehensive jewellery table shortcode class
    └── class-calculator.php      # Calculator shortcode class
```

## Shortcodes

### 1. Topbar Shortcode
Displays prices in a horizontal bar format.

**Usage:**
```
[gold_price_topbar]
[gold_price_topbar show_date="yes" show_time="yes"]
[gold_price_topbar show_date="no" show_time="yes"]
```

**Parameters:**
- `show_date` - Display date (yes/no) - Default: yes
- `show_time` - Display time (yes/no) - Default: yes

### 2. Spot Price Table Shortcode
Displays spot prices in a formatted table with per-gram pricing.

**Usage:**
```
[gold_price_table]
[gold_price_table title="SPOT PRICE PER GRAM" show_updated_time="yes"]
```

**Parameters:**
- `title` - Table title - Default: "SPOT PRICE PER GRAM"
- `show_updated_time` - Show update timestamp (yes/no) - Default: yes

### 3. Premium Jewellery Table Shortcode
Displays premium jewellery prices by karat (24kt, 22kt, 21kt, 18kt, 14kt, 10kt, 9kt).

**Usage:**
```
[gold_price_premium_jewellery]
[gold_price_premium_jewellery title="PREMIUM JEWELLERY" show_disclaimer="yes"]
```

**Parameters:**
- `title` - Table title - Default: "PREMIUM JEWELLERY"
- `show_disclaimer` - Show disclaimer text (yes/no) - Default: yes

**Karats Included:**
- 24kt Premium (99.9% pure gold)
- 22kt Premium (91.6% pure gold)
- 21kt Premium (87.5% pure gold)
- 18kt Premium (75.0% pure gold)
- 14kt Premium (58.5% pure gold)
- 10kt Premium (41.7% pure gold)
- 9kt Premium (37.5% pure gold)

### 4. Comprehensive Jewellery Table Shortcode
Displays complete jewellery pricing including Gold, Silver, and Platinum items.

**Usage:**
```
[gold_price_jewellery]
[gold_price_jewellery title="JEWELLERY" show_disclaimers="yes"]
```

**Parameters:**
- `title` - Table title - Default: "JEWELLERY"
- `show_disclaimers` - Show disclaimer texts (yes/no) - Default: yes

**Includes:**
- **Gold Jewellery**: 24kt, 22kt, 21kt, 18kt, 14kt, 10kt, 9kt, Gold Filled Items
- **Silver Jewellery**: Sterling Silver Flatware, Sterling Silver Jewellery, Mexican Silver Jewellery
- **Platinum Jewellery**: 999 Platinum, 950 Platinum

### 5. Scrap Metal Calculator Shortcode
Interactive calculator to estimate scrap metal value based on weight and purity.

**Usage:**
```
[gold_price_calculator]
[gold_price_calculator title="Scrap Metal Calculator" show_spot_price="yes"]
```

**Parameters:**
- `title` - Calculator title - Default: "Scrap Metal Calculator"
- `show_spot_price` - Show current spot price (yes/no) - Default: yes

**Features:**
- **Nice Select Dropdowns**: Beautiful, user-friendly select boxes
- **Metal Options**: Gold, Silver, Platinum
- **Purity Options**: 
  - Gold: 24kt, 22kt, 21kt, 18kt, 14kt, 10kt, 9kt
  - Silver: Sterling (92.5%), Fine (99.9%), Coin (90%)
  - Platinum: 999 (99.9%), 950 (95%), 900 (90%)
- **Real-time Calculation**: AJAX-powered instant results
- **Weight Input**: Enter weight in grams
- **Responsive Design**: Works on all devices

## API Information

**Endpoint:** `https://data-asg.goldprice.org/dbXRates/USD`

**Update Frequency:** Twice daily (12-hour cache)

**Metals Displayed:**
- Gold (XAU)
- Silver (XAG)
- Platinum (XPT)

**Update Frequency:**
- Automatic: Twice daily (12-hour cache)
- Manual: Click "Fetch Now" button in settings anytime

## Installation

1. Upload the `gold-price-lived` folder to `/wp-content/plugins/`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. **Configure API Key**: Go to Settings > Gold Price Live and enter your API key
4. Use shortcodes in any page, post, or widget

## Configuration

### Setting Up API Key

1. After activation, navigate to **Settings > Gold Price Live** in your WordPress admin
2. Enter your API key (get it from [goldprice.org](https://data-asg.goldprice.org))
   - The API key field is a password field for security
   - Click the eye icon (👁️) to toggle visibility if needed
   - The key is hidden by default to protect your credentials
3. Click "Save Settings"
4. The plugin will test the connection and display current gold price if successful
5. Once saved, the API key will be stored securely and hidden

**Security Features:**
- API key is displayed as password (hidden dots)
- Toggle visibility button to show/hide the key when needed
- Confirmation message when API key is configured
- No plain text display in browser

**Note:** Without an API key, the shortcodes will display a message asking you to configure the key in settings.

### Manual Data Fetch

By default, the plugin fetches data automatically twice daily (every 12 hours). If you need to update prices immediately:

1. Go to **Settings > Gold Price Live**
2. Scroll to the "Current Status" section
3. Click the **"Fetch Now"** button
4. Fresh data will be fetched immediately and cache will be cleared
5. The "Last Updated" timestamp will show when data was last fetched

This is useful when:
- You need to display the most current prices immediately
- Testing the API connection
- After changing market conditions
- Before important price displays

## Requirements

- WordPress 5.0 or higher
- PHP 7.0 or higher
- Valid API key from goldprice.org

## Author

**Md Farok Ahmed**
- Website: https://farok.me

## License

GPL v2 or later
