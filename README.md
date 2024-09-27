# Surge Campaigns Extend Plugin

This Surge plugin adds a custom field in campaigns module and provides more features related to scheduling campaign event.

## Requirements
- Surge version: 4.x or higher
- PHP version: 7.4 or higher

## Installation Instructions

### 1. Download the Plugin
- Clone or download the plugin from the repository.

```bash
git clone https://github.com/Surge-Media/SurgeExtendedCampaignBundle.git
```

### 2. Upload the Plugin to Surge
- Upload the downloaded plugin folder to Surge's plugin directory: /plugins.
- If SurgeExtendedBundles is present in the /plugins directory, first move that directory out to /tmp
- Unizip the new plugin and make sure it is called SurgeExtendedBundles

### 3. Clear Surge Cache
- After uploading the plugin, clear Surge's cache to recognize the plugin.
```bash
php /path-to-your-surge-installation/bin/console cache:clear
```

### 4. Generate Assets
- Generate assests
```bash
php /path-to-your-surge-installation/bin/console m:a:g
```

### 4. Install the Plugin
- Log in to your Surge dashboard.
- Navigate to Settings > Plugins.
- Click the "Install/Update" button.
