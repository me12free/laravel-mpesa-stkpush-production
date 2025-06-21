# Mpesa Laravel STK Push Premium

[![CI](https://github.com/me12free/laravel-mpesa-stkpush-production/actions/workflows/ci.yml/badge.svg)](https://github.com/me12free/laravel-mpesa-stkpush-production/actions)
[![Sponsor](https://img.shields.io/badge/sponsor-❤-brightgreen)](https://buymeacoffee.com/johnekiru7v)

A premium Laravel package for secure, production-ready M-Pesa STK Push integration.

## 100% Free & Open for Contributions
This package is **fully free to use and open for contributions**. There are no plans to introduce a commercial license or restrict usage. You are encouraged to use, modify, and contribute to the package.

## Support & Sponsorship
If you find this package useful, you can support development and motivate the author by [buying a coffee](https://buymeacoffee.com/johnekiru7v).

## Premium Services
- **Quick/Advanced Support:** For urgent help, advanced support, or customizations, please [open an issue](https://github.com/me12free/laravel-mpesa-stkpush-production/issues) or sponsor via BuyMeACoffee. You can also email **johnewoi72@gmail.com** for direct support.
- **Customization:** Custom features and integrations are available as a premium service. Contact via issues, BuyMeACoffee, or email **johnewoi72@gmail.com** for details.

## Contributions
Contributions are welcome! Please open issues or pull requests to help improve the package.

## Monetization & Premium
- **Sponsorship:** Support ongoing development via [BuyMeACoffee](https://buymeacoffee.com/johnekiru7v).
- **Powered by Link:** By default, a small "Powered by M-Pesa Premium" link appears on the payment form. You can disable it in the config.

## Quick Start

```bash
composer require me12free/laravel-mpesa-stkpush-production
php artisan vendor:publish --provider="MpesaPremium\\MpesaPremiumServiceProvider"
```

Set your credentials in `.env` (see below), then use the service or controller to initiate payments.

## Configuration

Publish the config file and set your production credentials:

```bash
php artisan vendor:publish --provider="MpesaPremium\\MpesaPremiumServiceProvider"
```

Set these in your `.env`:

```
MPESA_STK_ENDPOINT=https://api.safaricom.co.ke/mpesa/stkpush/v1/processrequest
MPESA_OAUTH_ENDPOINT=https://api.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials
MPESA_CONSUMER_KEY=your_production_key
MPESA_CONSUMER_SECRET=your_production_secret
MPESA_SHORTCODE=your_shortcode
MPESA_PASSKEY=your_passkey
MPESA_CALLBACK_URL=https://yourdomain.com/api/mpesa/callback?secret=your_shared_secret
MPESA_CALLBACK_SECRET=your_shared_secret
```

### Branding/Upgrade Link
To remove the "Powered by" or "Upgrade" link, set in your `config/mpesa-stkpush.php`:

```php
'branding' => [
    'powered_by' => true, // Show "Powered by" link
    'upgrade_link' => true, // Show "Upgrade to Premium" link
],
```

## Premium Add-ons
- [ ] SaaS dashboard integration (coming soon)
- [ ] Advanced reporting (coming soon)
- [ ] More payment gateways (coming soon)

## Demo
![Demo GIF](https://raw.githubusercontent.com/me12free/laravel-mpesa-stkpush-production/main/docs/demo.gif)

## Security
If you discover any security vulnerability, please open an [issue](https://github.com/me12free/laravel-mpesa-stkpush-production/issues). Do **not** disclose it publicly until it has been addressed.

## License
This package is fully free and open for all use. For premium support or customization, please use BuyMeACoffee, open an issue, or email **johnewoi72@gmail.com**.

For premium support, onboarding, or urgent help, open an [issue](https://github.com/me12free/laravel-mpesa-stkpush-production/issues), contact [@me12free on GitHub](https://github.com/me12free), or email **johnewoi72@gmail.com**.

---

## 📖 Full Integration Guide

For a comprehensive, step-by-step guide to M-Pesa STK Push integration in Laravel (including best practices, troubleshooting, and advanced topics), see:

- [Laravel M-Pesa STK Push Integration Guide](https://github.com/me12free/mpesa-laravel-guide)

---

## Security Best Practices
- Always use HTTPS in production
- Restrict callbacks to Safaricom IPs
- Use strong, unique secrets
- Enable 2FA for all admin users
- Never log sensitive credentials

## Advanced Usage
- Extend the Payment model for your own business logic
- Customize the view for your brand
- Add additional payment gateways as needed
- Use Laravel events to trigger notifications on payment status

## Support & Community
For premium support, onboarding, or urgent help, contact: **johnewoi72@gmail.com** or [@me12free on GitHub](https://github.com/me12free).

- [Join the discussion](https://github.com/me12free/mpesa-laravel-guide/discussions)
- [Rate & React](https://github.com/me12free/mpesa-laravel-guide)

## Contributing
This package is fully free and open for contributions! Please open issues or pull requests on GitHub to help improve the package.

For partnership or advanced collaboration inquiries, you can email **johnewoi72@gmail.com** or open an issue on the [GitHub repository](https://github.com/me12free/laravel-mpesa-stkpush-production).
