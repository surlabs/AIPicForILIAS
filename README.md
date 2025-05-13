# AIPic

# AIPic Page Component Plugin for ILIAS 9

Welcome to the official repository for AIPic Page Component Plugin for ILIAS

## What is AIPic for ILIAS?

AIPicForILIAS is a plugin that allow users to create images using artificial intelligence.

### Installation steps

1. Create subdirectories, if necessary for Customizing/global/plugins/Services/COPage/PageComponent/ or run the following script from the ILIAS root

```bash
mkdir -p Customizing/global/plugins/Services/COPage/PageComponent
cd Customizing/global/plugins/Services/COPage/PageComponent
```

2. Clone the repository

```bash
git clone https://github.com/surlabs/AIPic.git ./AIPic
cd AIPic
git checkout main
```

3. Ensure you run composer and npm install at platform root before you install/update the plugin:

```bash
composer install --no-dev
npm install
```

4. Run ILIAS update script at platform root:

```bash
php setup/setup.php update
```

**Ensure you don't ignore plugins at the ilias .gitignore files and don't use --no-plugins option at ILIAS setup**

5. Go to the ILIAS Plugin Administration and install/activate the plugin.
6. Go to the ILIAS Plugin Administration and configure the plugin.
7. Ready to use.

# Authors

- Initially created by SURLABS, spain [SURLABS](https://surlabs.com)
- Maintained by SURLABS, spain [SURLABS](https://surlabs.com)
