# AImageGenerator

# AImageGenerator Page Component Plugin for ILIAS 9

Welcome to the official repository for AImageGenerator Page Component Plugin for ILIAS

## What is AImageGenerator for ILIAS?

AImageGeneratorForILIAS is a plugin that allow users to create images using artificial intelligence.

### Installation steps


1. Create subdirectories, if necessary for Customizing/global/plugins/Services/COPage/PageComponent/ or run the following script fron the ILIAS root

```bash
mkdir -p Customizing/global/plugins/Services/COPage/PageComponent
cd Customizing/global/plugins/Services/COPage/PageComponent
```

3. In Customizing/global/plugins/Services/COPage/PageComponent/
4. Then, execute:

```bash
git clone https://github.com/surlabs/AImageGenerator.git ./AImageGenerator
cd AImageGenerator
git checkout main
```

Ensure you run composer and npm install at platform root before you install/update the plugin
```bash
composer install --no-dev
npm install
```

Run ILIAS update script at platform root
```bash
php setup/setup.php update
```
**Ensure you don't ignore plugins at the ilias .gitignore files and don't use --no-plugins option at ILIAS setup**

5. Go to the ILIAS Plugin Administration and install/activate the plugin.
6. Go to the ILIAS Plugin Administration and configure the plugin.
7. Ready to use.

# Authors
* Initially created by by SURLABS, spain [SURLABS](https://surlabs.com)
* Maintained by SURLABS, spain [SURLABS](https://surlabs.com)


