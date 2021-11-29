<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitb8ccebde2f0f0c5c17c736d9547cc8a5
{
    public static $files = array (
        'fda73876e8be17735f680f484cec1679' => __DIR__ . '/../..' . '/src/Functions/custom-settings-api.php',
        'c665fdac59180654d68b0973da57cb88' => __DIR__ . '/../..' . '/src/Functions/GlobalFunctions.php',
    );

    public static $prefixLengthsPsr4 = array (
        'S' => 
        array (
            'Symfony\\Component\\CssSelector\\' => 30,
        ),
        'P' => 
        array (
            'ProfilePress\\Core\\' => 18,
            'Pelago\\' => 7,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Symfony\\Component\\CssSelector\\' => 
        array (
            0 => __DIR__ . '/..' . '/symfony/css-selector',
        ),
        'ProfilePress\\Core\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src',
        ),
        'Pelago\\' => 
        array (
            0 => __DIR__ . '/..' . '/pelago/emogrifier/src',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
        'PAnD' => __DIR__ . '/..' . '/collizo4sky/persist-admin-notices-dismissal/persist-admin-notices-dismissal.php',
        'Pelago\\Emogrifier' => __DIR__ . '/..' . '/pelago/emogrifier/src/Emogrifier.php',
        'Pelago\\Emogrifier\\CssInliner' => __DIR__ . '/..' . '/pelago/emogrifier/src/Emogrifier/CssInliner.php',
        'Pelago\\Emogrifier\\HtmlProcessor\\AbstractHtmlProcessor' => __DIR__ . '/..' . '/pelago/emogrifier/src/Emogrifier/HtmlProcessor/AbstractHtmlProcessor.php',
        'Pelago\\Emogrifier\\HtmlProcessor\\CssToAttributeConverter' => __DIR__ . '/..' . '/pelago/emogrifier/src/Emogrifier/HtmlProcessor/CssToAttributeConverter.php',
        'Pelago\\Emogrifier\\HtmlProcessor\\HtmlNormalizer' => __DIR__ . '/..' . '/pelago/emogrifier/src/Emogrifier/HtmlProcessor/HtmlNormalizer.php',
        'Pelago\\Emogrifier\\HtmlProcessor\\HtmlPruner' => __DIR__ . '/..' . '/pelago/emogrifier/src/Emogrifier/HtmlProcessor/HtmlPruner.php',
        'Pelago\\Emogrifier\\Utilities\\ArrayIntersector' => __DIR__ . '/..' . '/pelago/emogrifier/src/Emogrifier/Utilities/ArrayIntersector.php',
        'Pelago\\Emogrifier\\Utilities\\CssConcatenator' => __DIR__ . '/..' . '/pelago/emogrifier/src/Emogrifier/Utilities/CssConcatenator.php',
        'ProfilePress\\Core\\AdminBarDashboardAccess\\Init' => __DIR__ . '/../..' . '/src/AdminBarDashboardAccess/Init.php',
        'ProfilePress\\Core\\Admin\\SettingsPages\\AbstractSettingsPage' => __DIR__ . '/../..' . '/src/Admin/SettingsPages/AbstractSettingsPage.php',
        'ProfilePress\\Core\\Admin\\SettingsPages\\AddNewForm' => __DIR__ . '/../..' . '/src/Admin/SettingsPages/AddNewForm.php',
        'ProfilePress\\Core\\Admin\\SettingsPages\\AdminFooter' => __DIR__ . '/../..' . '/src/Admin/SettingsPages/AdminFooter.php',
        'ProfilePress\\Core\\Admin\\SettingsPages\\DragDropBuilder\\Controls\\IconPicker' => __DIR__ . '/../..' . '/src/Admin/SettingsPages/DragDropBuilder/Controls/IconPicker.php',
        'ProfilePress\\Core\\Admin\\SettingsPages\\DragDropBuilder\\Controls\\Input' => __DIR__ . '/../..' . '/src/Admin/SettingsPages/DragDropBuilder/Controls/Input.php',
        'ProfilePress\\Core\\Admin\\SettingsPages\\DragDropBuilder\\Controls\\Select' => __DIR__ . '/../..' . '/src/Admin/SettingsPages/DragDropBuilder/Controls/Select.php',
        'ProfilePress\\Core\\Admin\\SettingsPages\\DragDropBuilder\\Controls\\Textarea' => __DIR__ . '/../..' . '/src/Admin/SettingsPages/DragDropBuilder/Controls/Textarea.php',
        'ProfilePress\\Core\\Admin\\SettingsPages\\DragDropBuilder\\Controls\\WPEditor' => __DIR__ . '/../..' . '/src/Admin/SettingsPages/DragDropBuilder/Controls/WPEditor.php',
        'ProfilePress\\Core\\Admin\\SettingsPages\\DragDropBuilder\\DragDropBuilder' => __DIR__ . '/../..' . '/src/Admin/SettingsPages/DragDropBuilder/DragDropBuilder.php',
        'ProfilePress\\Core\\Admin\\SettingsPages\\DragDropBuilder\\FieldBase' => __DIR__ . '/../..' . '/src/Admin/SettingsPages/DragDropBuilder/FieldBase.php',
        'ProfilePress\\Core\\Admin\\SettingsPages\\DragDropBuilder\\FieldInterface' => __DIR__ . '/../..' . '/src/Admin/SettingsPages/DragDropBuilder/FieldInterface.php',
        'ProfilePress\\Core\\Admin\\SettingsPages\\DragDropBuilder\\Fields\\Bio' => __DIR__ . '/../..' . '/src/Admin/SettingsPages/DragDropBuilder/Fields/Bio.php',
        'ProfilePress\\Core\\Admin\\SettingsPages\\DragDropBuilder\\Fields\\CFPassword' => __DIR__ . '/../..' . '/src/Admin/SettingsPages/DragDropBuilder/Fields/CFPassword.php',
        'ProfilePress\\Core\\Admin\\SettingsPages\\DragDropBuilder\\Fields\\CheckboxList' => __DIR__ . '/../..' . '/src/Admin/SettingsPages/DragDropBuilder/Fields/CheckboxList.php',
        'ProfilePress\\Core\\Admin\\SettingsPages\\DragDropBuilder\\Fields\\ConfirmEmail' => __DIR__ . '/../..' . '/src/Admin/SettingsPages/DragDropBuilder/Fields/ConfirmEmail.php',
        'ProfilePress\\Core\\Admin\\SettingsPages\\DragDropBuilder\\Fields\\ConfirmPassword' => __DIR__ . '/../..' . '/src/Admin/SettingsPages/DragDropBuilder/Fields/ConfirmPassword.php',
        'ProfilePress\\Core\\Admin\\SettingsPages\\DragDropBuilder\\Fields\\Country' => __DIR__ . '/../..' . '/src/Admin/SettingsPages/DragDropBuilder/Fields/Country.php',
        'ProfilePress\\Core\\Admin\\SettingsPages\\DragDropBuilder\\Fields\\CoverImage' => __DIR__ . '/../..' . '/src/Admin/SettingsPages/DragDropBuilder/Fields/CoverImage.php',
        'ProfilePress\\Core\\Admin\\SettingsPages\\DragDropBuilder\\Fields\\Date' => __DIR__ . '/../..' . '/src/Admin/SettingsPages/DragDropBuilder/Fields/Date.php',
        'ProfilePress\\Core\\Admin\\SettingsPages\\DragDropBuilder\\Fields\\DefinedFieldTypes\\Agreeable' => __DIR__ . '/../..' . '/src/Admin/SettingsPages/DragDropBuilder/Fields/DefinedFieldTypes/Agreeable.php',
        'ProfilePress\\Core\\Admin\\SettingsPages\\DragDropBuilder\\Fields\\DefinedFieldTypes\\Checkbox' => __DIR__ . '/../..' . '/src/Admin/SettingsPages/DragDropBuilder/Fields/DefinedFieldTypes/Checkbox.php',
        'ProfilePress\\Core\\Admin\\SettingsPages\\DragDropBuilder\\Fields\\DefinedFieldTypes\\Date' => __DIR__ . '/../..' . '/src/Admin/SettingsPages/DragDropBuilder/Fields/DefinedFieldTypes/Date.php',
        'ProfilePress\\Core\\Admin\\SettingsPages\\DragDropBuilder\\Fields\\DefinedFieldTypes\\Input' => __DIR__ . '/../..' . '/src/Admin/SettingsPages/DragDropBuilder/Fields/DefinedFieldTypes/Input.php',
        'ProfilePress\\Core\\Admin\\SettingsPages\\DragDropBuilder\\Fields\\DefinedFieldTypes\\Password' => __DIR__ . '/../..' . '/src/Admin/SettingsPages/DragDropBuilder/Fields/DefinedFieldTypes/Password.php',
        'ProfilePress\\Core\\Admin\\SettingsPages\\DragDropBuilder\\Fields\\DefinedFieldTypes\\Radio' => __DIR__ . '/../..' . '/src/Admin/SettingsPages/DragDropBuilder/Fields/DefinedFieldTypes/Radio.php',
        'ProfilePress\\Core\\Admin\\SettingsPages\\DragDropBuilder\\Fields\\DefinedFieldTypes\\Select' => __DIR__ . '/../..' . '/src/Admin/SettingsPages/DragDropBuilder/Fields/DefinedFieldTypes/Select.php',
        'ProfilePress\\Core\\Admin\\SettingsPages\\DragDropBuilder\\Fields\\DisplayName' => __DIR__ . '/../..' . '/src/Admin/SettingsPages/DragDropBuilder/Fields/DisplayName.php',
        'ProfilePress\\Core\\Admin\\SettingsPages\\DragDropBuilder\\Fields\\EditProfile\\ShowCoverImage' => __DIR__ . '/../..' . '/src/Admin/SettingsPages/DragDropBuilder/Fields/EditProfile/ShowCoverImage.php',
        'ProfilePress\\Core\\Admin\\SettingsPages\\DragDropBuilder\\Fields\\EditProfile\\ShowProfilePicture' => __DIR__ . '/../..' . '/src/Admin/SettingsPages/DragDropBuilder/Fields/EditProfile/ShowProfilePicture.php',
        'ProfilePress\\Core\\Admin\\SettingsPages\\DragDropBuilder\\Fields\\Email' => __DIR__ . '/../..' . '/src/Admin/SettingsPages/DragDropBuilder/Fields/Email.php',
        'ProfilePress\\Core\\Admin\\SettingsPages\\DragDropBuilder\\Fields\\FirstName' => __DIR__ . '/../..' . '/src/Admin/SettingsPages/DragDropBuilder/Fields/FirstName.php',
        'ProfilePress\\Core\\Admin\\SettingsPages\\DragDropBuilder\\Fields\\HTML' => __DIR__ . '/../..' . '/src/Admin/SettingsPages/DragDropBuilder/Fields/HTML.php',
        'ProfilePress\\Core\\Admin\\SettingsPages\\DragDropBuilder\\Fields\\Init' => __DIR__ . '/../..' . '/src/Admin/SettingsPages/DragDropBuilder/Fields/Init.php',
        'ProfilePress\\Core\\Admin\\SettingsPages\\DragDropBuilder\\Fields\\LastName' => __DIR__ . '/../..' . '/src/Admin/SettingsPages/DragDropBuilder/Fields/LastName.php',
        'ProfilePress\\Core\\Admin\\SettingsPages\\DragDropBuilder\\Fields\\Login\\Password' => __DIR__ . '/../..' . '/src/Admin/SettingsPages/DragDropBuilder/Fields/Login/Password.php',
        'ProfilePress\\Core\\Admin\\SettingsPages\\DragDropBuilder\\Fields\\Login\\RememberLogin' => __DIR__ . '/../..' . '/src/Admin/SettingsPages/DragDropBuilder/Fields/Login/RememberLogin.php',
        'ProfilePress\\Core\\Admin\\SettingsPages\\DragDropBuilder\\Fields\\Login\\Userlogin' => __DIR__ . '/../..' . '/src/Admin/SettingsPages/DragDropBuilder/Fields/Login/Userlogin.php',
        'ProfilePress\\Core\\Admin\\SettingsPages\\DragDropBuilder\\Fields\\Nickname' => __DIR__ . '/../..' . '/src/Admin/SettingsPages/DragDropBuilder/Fields/Nickname.php',
        'ProfilePress\\Core\\Admin\\SettingsPages\\DragDropBuilder\\Fields\\Number' => __DIR__ . '/../..' . '/src/Admin/SettingsPages/DragDropBuilder/Fields/Number.php',
        'ProfilePress\\Core\\Admin\\SettingsPages\\DragDropBuilder\\Fields\\Password' => __DIR__ . '/../..' . '/src/Admin/SettingsPages/DragDropBuilder/Fields/Password.php',
        'ProfilePress\\Core\\Admin\\SettingsPages\\DragDropBuilder\\Fields\\PasswordReset\\Userlogin' => __DIR__ . '/../..' . '/src/Admin/SettingsPages/DragDropBuilder/Fields/PasswordReset/Userlogin.php',
        'ProfilePress\\Core\\Admin\\SettingsPages\\DragDropBuilder\\Fields\\PasswordStrengthMeter' => __DIR__ . '/../..' . '/src/Admin/SettingsPages/DragDropBuilder/Fields/PasswordStrengthMeter.php',
        'ProfilePress\\Core\\Admin\\SettingsPages\\DragDropBuilder\\Fields\\ProfilePicture' => __DIR__ . '/../..' . '/src/Admin/SettingsPages/DragDropBuilder/Fields/ProfilePicture.php',
        'ProfilePress\\Core\\Admin\\SettingsPages\\DragDropBuilder\\Fields\\RadioButtons' => __DIR__ . '/../..' . '/src/Admin/SettingsPages/DragDropBuilder/Fields/RadioButtons.php',
        'ProfilePress\\Core\\Admin\\SettingsPages\\DragDropBuilder\\Fields\\SelectDropdown' => __DIR__ . '/../..' . '/src/Admin/SettingsPages/DragDropBuilder/Fields/SelectDropdown.php',
        'ProfilePress\\Core\\Admin\\SettingsPages\\DragDropBuilder\\Fields\\SelectRole' => __DIR__ . '/../..' . '/src/Admin/SettingsPages/DragDropBuilder/Fields/SelectRole.php',
        'ProfilePress\\Core\\Admin\\SettingsPages\\DragDropBuilder\\Fields\\SingleCheckbox' => __DIR__ . '/../..' . '/src/Admin/SettingsPages/DragDropBuilder/Fields/SingleCheckbox.php',
        'ProfilePress\\Core\\Admin\\SettingsPages\\DragDropBuilder\\Fields\\TextBox' => __DIR__ . '/../..' . '/src/Admin/SettingsPages/DragDropBuilder/Fields/TextBox.php',
        'ProfilePress\\Core\\Admin\\SettingsPages\\DragDropBuilder\\Fields\\Textarea' => __DIR__ . '/../..' . '/src/Admin/SettingsPages/DragDropBuilder/Fields/Textarea.php',
        'ProfilePress\\Core\\Admin\\SettingsPages\\DragDropBuilder\\Fields\\UserProfile\\Bio' => __DIR__ . '/../..' . '/src/Admin/SettingsPages/DragDropBuilder/Fields/UserProfile/Bio.php',
        'ProfilePress\\Core\\Admin\\SettingsPages\\DragDropBuilder\\Fields\\UserProfile\\CustomField' => __DIR__ . '/../..' . '/src/Admin/SettingsPages/DragDropBuilder/Fields/UserProfile/CustomField.php',
        'ProfilePress\\Core\\Admin\\SettingsPages\\DragDropBuilder\\Fields\\UserProfile\\DisplayName' => __DIR__ . '/../..' . '/src/Admin/SettingsPages/DragDropBuilder/Fields/UserProfile/DisplayName.php',
        'ProfilePress\\Core\\Admin\\SettingsPages\\DragDropBuilder\\Fields\\UserProfile\\Email' => __DIR__ . '/../..' . '/src/Admin/SettingsPages/DragDropBuilder/Fields/UserProfile/Email.php',
        'ProfilePress\\Core\\Admin\\SettingsPages\\DragDropBuilder\\Fields\\UserProfile\\FirstName' => __DIR__ . '/../..' . '/src/Admin/SettingsPages/DragDropBuilder/Fields/UserProfile/FirstName.php',
        'ProfilePress\\Core\\Admin\\SettingsPages\\DragDropBuilder\\Fields\\UserProfile\\LastName' => __DIR__ . '/../..' . '/src/Admin/SettingsPages/DragDropBuilder/Fields/UserProfile/LastName.php',
        'ProfilePress\\Core\\Admin\\SettingsPages\\DragDropBuilder\\Fields\\UserProfile\\Nickname' => __DIR__ . '/../..' . '/src/Admin/SettingsPages/DragDropBuilder/Fields/UserProfile/Nickname.php',
        'ProfilePress\\Core\\Admin\\SettingsPages\\DragDropBuilder\\Fields\\UserProfile\\Username' => __DIR__ . '/../..' . '/src/Admin/SettingsPages/DragDropBuilder/Fields/UserProfile/Username.php',
        'ProfilePress\\Core\\Admin\\SettingsPages\\DragDropBuilder\\Fields\\UserProfile\\Website' => __DIR__ . '/../..' . '/src/Admin/SettingsPages/DragDropBuilder/Fields/UserProfile/Website.php',
        'ProfilePress\\Core\\Admin\\SettingsPages\\DragDropBuilder\\Fields\\Username' => __DIR__ . '/../..' . '/src/Admin/SettingsPages/DragDropBuilder/Fields/Username.php',
        'ProfilePress\\Core\\Admin\\SettingsPages\\DragDropBuilder\\Fields\\Website' => __DIR__ . '/../..' . '/src/Admin/SettingsPages/DragDropBuilder/Fields/Website.php',
        'ProfilePress\\Core\\Admin\\SettingsPages\\DragDropBuilder\\Metabox' => __DIR__ . '/../..' . '/src/Admin/SettingsPages/DragDropBuilder/Metabox.php',
        'ProfilePress\\Core\\Admin\\SettingsPages\\EmailSettings\\CustomizerTrait' => __DIR__ . '/../..' . '/src/Admin/SettingsPages/EmailSettings/CustomizerTrait.php',
        'ProfilePress\\Core\\Admin\\SettingsPages\\EmailSettings\\DefaultTemplateCustomizer' => __DIR__ . '/../..' . '/src/Admin/SettingsPages/EmailSettings/DefaultTemplateCustomizer.php',
        'ProfilePress\\Core\\Admin\\SettingsPages\\EmailSettings\\EmailSettingsPage' => __DIR__ . '/../..' . '/src/Admin/SettingsPages/EmailSettings/EmailSettingsPage.php',
        'ProfilePress\\Core\\Admin\\SettingsPages\\EmailSettings\\WPListTable' => __DIR__ . '/../..' . '/src/Admin/SettingsPages/EmailSettings/WPListTable.php',
        'ProfilePress\\Core\\Admin\\SettingsPages\\ExtensionsSettingsPage' => __DIR__ . '/../..' . '/src/Admin/SettingsPages/ExtensionsSettingsPage.php',
        'ProfilePress\\Core\\Admin\\SettingsPages\\FormList' => __DIR__ . '/../..' . '/src/Admin/SettingsPages/FormList.php',
        'ProfilePress\\Core\\Admin\\SettingsPages\\Forms' => __DIR__ . '/../..' . '/src/Admin/SettingsPages/Forms.php',
        'ProfilePress\\Core\\Admin\\SettingsPages\\GeneralSettings' => __DIR__ . '/../..' . '/src/Admin/SettingsPages/GeneralSettings.php',
        'ProfilePress\\Core\\Admin\\SettingsPages\\IDUserColumn' => __DIR__ . '/../..' . '/src/Admin/SettingsPages/IDUserColumn.php',
        'ProfilePress\\Core\\Admin\\SettingsPages\\MailOptin' => __DIR__ . '/../..' . '/src/Admin/SettingsPages/MailOptin.php',
        'ProfilePress\\Core\\Admin\\SettingsPages\\MemberDirectories' => __DIR__ . '/../..' . '/src/Admin/SettingsPages/MemberDirectories.php',
        'ProfilePress\\Core\\Admin\\SettingsPages\\MembersDirectoryList' => __DIR__ . '/../..' . '/src/Admin/SettingsPages/MembersDirectoryList.php',
        'ProfilePress\\Core\\Admin\\SettingsPages\\ToolsSettingsPage' => __DIR__ . '/../..' . '/src/Admin/SettingsPages/ToolsSettingsPage.php',
        'ProfilePress\\Core\\Base' => __DIR__ . '/../..' . '/src/Base.php',
        'ProfilePress\\Core\\Classes\\AdminNotices' => __DIR__ . '/../..' . '/src/Classes/AdminNotices.php',
        'ProfilePress\\Core\\Classes\\AjaxHandler' => __DIR__ . '/../..' . '/src/Classes/AjaxHandler.php',
        'ProfilePress\\Core\\Classes\\Autologin' => __DIR__ . '/../..' . '/src/Classes/Autologin.php',
        'ProfilePress\\Core\\Classes\\BuddyPressBbPress' => __DIR__ . '/../..' . '/src/Classes/BuddyPressBbPress.php',
        'ProfilePress\\Core\\Classes\\EditUserProfile' => __DIR__ . '/../..' . '/src/Classes/EditUserProfile.php',
        'ProfilePress\\Core\\Classes\\ExtensionManager' => __DIR__ . '/../..' . '/src/Classes/ExtensionManager.php',
        'ProfilePress\\Core\\Classes\\FileUploader' => __DIR__ . '/../..' . '/src/Classes/FileUploader.php',
        'ProfilePress\\Core\\Classes\\FormPreviewHandler' => __DIR__ . '/../..' . '/src/Classes/FormPreviewHandler.php',
        'ProfilePress\\Core\\Classes\\FormRepository' => __DIR__ . '/../..' . '/src/Classes/FormRepository.php',
        'ProfilePress\\Core\\Classes\\FormShortcodeDefaults' => __DIR__ . '/../..' . '/src/Classes/FormShortcodeDefaults.php',
        'ProfilePress\\Core\\Classes\\GDPR' => __DIR__ . '/../..' . '/src/Classes/GDPR.php',
        'ProfilePress\\Core\\Classes\\GlobalSiteAccess' => __DIR__ . '/../..' . '/src/Classes/GlobalSiteAccess.php',
        'ProfilePress\\Core\\Classes\\ImageUploader' => __DIR__ . '/../..' . '/src/Classes/ImageUploader.php',
        'ProfilePress\\Core\\Classes\\Installer\\PPress_Install_Skin' => __DIR__ . '/../..' . '/src/Classes/Installer/PPress_Install_Skin.php',
        'ProfilePress\\Core\\Classes\\Installer\\PluginSilentUpgrader' => __DIR__ . '/../..' . '/src/Classes/Installer/PluginSilentUpgrader.php',
        'ProfilePress\\Core\\Classes\\Installer\\PluginSilentUpgraderSkin' => __DIR__ . '/../..' . '/src/Classes/Installer/PluginSilentUpgraderSkin.php',
        'ProfilePress\\Core\\Classes\\LoginAuth' => __DIR__ . '/../..' . '/src/Classes/LoginAuth.php',
        'ProfilePress\\Core\\Classes\\Miscellaneous' => __DIR__ . '/../..' . '/src/Classes/Miscellaneous.php',
        'ProfilePress\\Core\\Classes\\ModifyRedirectDefaultLinks' => __DIR__ . '/../..' . '/src/Classes/ModifyRedirectDefaultLinks.php',
        'ProfilePress\\Core\\Classes\\PPRESS_Session' => __DIR__ . '/../..' . '/src/Classes/PPRESS_Session.php',
        'ProfilePress\\Core\\Classes\\PROFILEPRESS_sql' => __DIR__ . '/../..' . '/src/Classes/PROFILEPRESS_sql.php',
        'ProfilePress\\Core\\Classes\\PasswordReset' => __DIR__ . '/../..' . '/src/Classes/PasswordReset.php',
        'ProfilePress\\Core\\Classes\\ProfileUrlRewrite' => __DIR__ . '/../..' . '/src/Classes/ProfileUrlRewrite.php',
        'ProfilePress\\Core\\Classes\\RegistrationAuth' => __DIR__ . '/../..' . '/src/Classes/RegistrationAuth.php',
        'ProfilePress\\Core\\Classes\\SendEmail' => __DIR__ . '/../..' . '/src/Classes/SendEmail.php',
        'ProfilePress\\Core\\Classes\\ShortcodeThemeFactory' => __DIR__ . '/../..' . '/src/Classes/ShortcodeThemeFactory.php',
        'ProfilePress\\Core\\Classes\\UserAvatar' => __DIR__ . '/../..' . '/src/Classes/UserAvatar.php',
        'ProfilePress\\Core\\Classes\\UserSignupLocationListingPage' => __DIR__ . '/../..' . '/src/Classes/UserSignupLocationListingPage.php',
        'ProfilePress\\Core\\Classes\\UsernameEmailRestrictLogin' => __DIR__ . '/../..' . '/src/Classes/UsernameEmailRestrictLogin.php',
        'ProfilePress\\Core\\Classes\\WelcomeEmailAfterSignup' => __DIR__ . '/../..' . '/src/Classes/WelcomeEmailAfterSignup.php',
        'ProfilePress\\Core\\ContentProtection\\ConditionCallbacks' => __DIR__ . '/../..' . '/src/ContentProtection/ConditionCallbacks.php',
        'ProfilePress\\Core\\ContentProtection\\ContentConditions' => __DIR__ . '/../..' . '/src/ContentProtection/ContentConditions.php',
        'ProfilePress\\Core\\ContentProtection\\Frontend\\Checker' => __DIR__ . '/../..' . '/src/ContentProtection/Frontend/Checker.php',
        'ProfilePress\\Core\\ContentProtection\\Frontend\\PostContent' => __DIR__ . '/../..' . '/src/ContentProtection/Frontend/PostContent.php',
        'ProfilePress\\Core\\ContentProtection\\Frontend\\Redirect' => __DIR__ . '/../..' . '/src/ContentProtection/Frontend/Redirect.php',
        'ProfilePress\\Core\\ContentProtection\\Init' => __DIR__ . '/../..' . '/src/ContentProtection/Init.php',
        'ProfilePress\\Core\\ContentProtection\\SettingsPage' => __DIR__ . '/../..' . '/src/ContentProtection/SettingsPage.php',
        'ProfilePress\\Core\\ContentProtection\\WPListTable' => __DIR__ . '/../..' . '/src/ContentProtection/WPListTable.php',
        'ProfilePress\\Core\\DBUpdates' => __DIR__ . '/../..' . '/src/DBUpdates.php',
        'ProfilePress\\Core\\NavigationMenuLinks\\Backend' => __DIR__ . '/../..' . '/src/NavigationMenuLinks/Backend.php',
        'ProfilePress\\Core\\NavigationMenuLinks\\Frontend' => __DIR__ . '/../..' . '/src/NavigationMenuLinks/Frontend.php',
        'ProfilePress\\Core\\NavigationMenuLinks\\Init' => __DIR__ . '/../..' . '/src/NavigationMenuLinks/Init.php',
        'ProfilePress\\Core\\NavigationMenuLinks\\PP_Nav_Items' => __DIR__ . '/../..' . '/src/NavigationMenuLinks/PP_Nav_Items.php',
        'ProfilePress\\Core\\RegisterActivation\\Base' => __DIR__ . '/../..' . '/src/RegisterActivation/Base.php',
        'ProfilePress\\Core\\RegisterActivation\\CreateDBTables' => __DIR__ . '/../..' . '/src/RegisterActivation/CreateDBTables.php',
        'ProfilePress\\Core\\RegisterScripts' => __DIR__ . '/../..' . '/src/RegisterScripts.php',
        'ProfilePress\\Core\\ShortcodeParser\\Builder\\EditProfileBuilder' => __DIR__ . '/../..' . '/src/ShortcodeParser/Builder/EditProfileBuilder.php',
        'ProfilePress\\Core\\ShortcodeParser\\Builder\\FieldsShortcodeCallback' => __DIR__ . '/../..' . '/src/ShortcodeParser/Builder/FieldsShortcodeCallback.php',
        'ProfilePress\\Core\\ShortcodeParser\\Builder\\FrontendProfileBuilder' => __DIR__ . '/../..' . '/src/ShortcodeParser/Builder/FrontendProfileBuilder.php',
        'ProfilePress\\Core\\ShortcodeParser\\Builder\\GlobalShortcodes' => __DIR__ . '/../..' . '/src/ShortcodeParser/Builder/GlobalShortcodes.php',
        'ProfilePress\\Core\\ShortcodeParser\\Builder\\LoginFormBuilder' => __DIR__ . '/../..' . '/src/ShortcodeParser/Builder/LoginFormBuilder.php',
        'ProfilePress\\Core\\ShortcodeParser\\Builder\\PasswordResetBuilder' => __DIR__ . '/../..' . '/src/ShortcodeParser/Builder/PasswordResetBuilder.php',
        'ProfilePress\\Core\\ShortcodeParser\\Builder\\RegistrationFormBuilder' => __DIR__ . '/../..' . '/src/ShortcodeParser/Builder/RegistrationFormBuilder.php',
        'ProfilePress\\Core\\ShortcodeParser\\EditProfileTag' => __DIR__ . '/../..' . '/src/ShortcodeParser/EditProfileTag.php',
        'ProfilePress\\Core\\ShortcodeParser\\FormProcessor' => __DIR__ . '/../..' . '/src/ShortcodeParser/FormProcessor.php',
        'ProfilePress\\Core\\ShortcodeParser\\FrontendProfileTag' => __DIR__ . '/../..' . '/src/ShortcodeParser/FrontendProfileTag.php',
        'ProfilePress\\Core\\ShortcodeParser\\LoginFormTag' => __DIR__ . '/../..' . '/src/ShortcodeParser/LoginFormTag.php',
        'ProfilePress\\Core\\ShortcodeParser\\MelangeTag' => __DIR__ . '/../..' . '/src/ShortcodeParser/MelangeTag.php',
        'ProfilePress\\Core\\ShortcodeParser\\MemberDirectoryTag' => __DIR__ . '/../..' . '/src/ShortcodeParser/MemberDirectoryTag.php',
        'ProfilePress\\Core\\ShortcodeParser\\MyAccount\\MyAccountTag' => __DIR__ . '/../..' . '/src/ShortcodeParser/MyAccount/MyAccountTag.php',
        'ProfilePress\\Core\\ShortcodeParser\\PasswordResetTag' => __DIR__ . '/../..' . '/src/ShortcodeParser/PasswordResetTag.php',
        'ProfilePress\\Core\\ShortcodeParser\\RegistrationFormTag' => __DIR__ . '/../..' . '/src/ShortcodeParser/RegistrationFormTag.php',
        'ProfilePress\\Core\\Themes\\DragDrop\\AbstractBuildScratch' => __DIR__ . '/../..' . '/src/Themes/DragDrop/AbstractBuildScratch.php',
        'ProfilePress\\Core\\Themes\\DragDrop\\AbstractMemberDirectoryTheme' => __DIR__ . '/../..' . '/src/Themes/DragDrop/AbstractMemberDirectoryTheme.php',
        'ProfilePress\\Core\\Themes\\DragDrop\\AbstractTheme' => __DIR__ . '/../..' . '/src/Themes/DragDrop/AbstractTheme.php',
        'ProfilePress\\Core\\Themes\\DragDrop\\EditProfile\\BuildScratch' => __DIR__ . '/../..' . '/src/Themes/DragDrop/EditProfile/BuildScratch.php',
        'ProfilePress\\Core\\Themes\\DragDrop\\EditProfile\\Tulip' => __DIR__ . '/../..' . '/src/Themes/DragDrop/EditProfile/Tulip.php',
        'ProfilePress\\Core\\Themes\\DragDrop\\FieldListing' => __DIR__ . '/../..' . '/src/Themes/DragDrop/FieldListing.php',
        'ProfilePress\\Core\\Themes\\DragDrop\\Login\\BuildScratch' => __DIR__ . '/../..' . '/src/Themes/DragDrop/Login/BuildScratch.php',
        'ProfilePress\\Core\\Themes\\DragDrop\\Login\\PerfectoLite' => __DIR__ . '/../..' . '/src/Themes/DragDrop/Login/PerfectoLite.php',
        'ProfilePress\\Core\\Themes\\DragDrop\\Login\\Tulip' => __DIR__ . '/../..' . '/src/Themes/DragDrop/Login/Tulip.php',
        'ProfilePress\\Core\\Themes\\DragDrop\\MemberDirectoryListing' => __DIR__ . '/../..' . '/src/Themes/DragDrop/MemberDirectoryListing.php',
        'ProfilePress\\Core\\Themes\\DragDrop\\MemberDirectory\\DefaultTemplate' => __DIR__ . '/../..' . '/src/Themes/DragDrop/MemberDirectory/DefaultTemplate.php',
        'ProfilePress\\Core\\Themes\\DragDrop\\MemberDirectory\\Gerbera' => __DIR__ . '/../..' . '/src/Themes/DragDrop/MemberDirectory/Gerbera.php',
        'ProfilePress\\Core\\Themes\\DragDrop\\PasswordReset\\BuildScratch' => __DIR__ . '/../..' . '/src/Themes/DragDrop/PasswordReset/BuildScratch.php',
        'ProfilePress\\Core\\Themes\\DragDrop\\PasswordReset\\Tulip' => __DIR__ . '/../..' . '/src/Themes/DragDrop/PasswordReset/Tulip.php',
        'ProfilePress\\Core\\Themes\\DragDrop\\ProfileFieldListing' => __DIR__ . '/../..' . '/src/Themes/DragDrop/ProfileFieldListing.php',
        'ProfilePress\\Core\\Themes\\DragDrop\\Registration\\BuildScratch' => __DIR__ . '/../..' . '/src/Themes/DragDrop/Registration/BuildScratch.php',
        'ProfilePress\\Core\\Themes\\DragDrop\\Registration\\PerfectoLite' => __DIR__ . '/../..' . '/src/Themes/DragDrop/Registration/PerfectoLite.php',
        'ProfilePress\\Core\\Themes\\DragDrop\\Registration\\Tulip' => __DIR__ . '/../..' . '/src/Themes/DragDrop/Registration/Tulip.php',
        'ProfilePress\\Core\\Themes\\DragDrop\\ThemeInterface' => __DIR__ . '/../..' . '/src/Themes/DragDrop/ThemeInterface.php',
        'ProfilePress\\Core\\Themes\\DragDrop\\ThemesRepository' => __DIR__ . '/../..' . '/src/Themes/DragDrop/ThemesRepository.php',
        'ProfilePress\\Core\\Themes\\DragDrop\\UserProfile\\DefaultTemplate' => __DIR__ . '/../..' . '/src/Themes/DragDrop/UserProfile/DefaultTemplate.php',
        'ProfilePress\\Core\\Themes\\DragDrop\\UserProfile\\Dixon' => __DIR__ . '/../..' . '/src/Themes/DragDrop/UserProfile/Dixon.php',
        'ProfilePress\\Core\\Themes\\Shortcode\\EditProfileThemeInterface' => __DIR__ . '/../..' . '/src/Themes/Shortcode/EditProfileThemeInterface.php',
        'ProfilePress\\Core\\Themes\\Shortcode\\Editprofile\\Boson' => __DIR__ . '/../..' . '/src/Themes/Shortcode/Editprofile/Boson.php',
        'ProfilePress\\Core\\Themes\\Shortcode\\Editprofile\\Perfecto' => __DIR__ . '/../..' . '/src/Themes/Shortcode/Editprofile/Perfecto.php',
        'ProfilePress\\Core\\Themes\\Shortcode\\LoginThemeInterface' => __DIR__ . '/../..' . '/src/Themes/Shortcode/LoginThemeInterface.php',
        'ProfilePress\\Core\\Themes\\Shortcode\\Login\\Fzbuk' => __DIR__ . '/../..' . '/src/Themes/Shortcode/Login/Fzbuk.php',
        'ProfilePress\\Core\\Themes\\Shortcode\\Login\\PerfectoLite' => __DIR__ . '/../..' . '/src/Themes/Shortcode/Login/PerfectoLite.php',
        'ProfilePress\\Core\\Themes\\Shortcode\\MelangeThemeInterface' => __DIR__ . '/../..' . '/src/Themes/Shortcode/MelangeThemeInterface.php',
        'ProfilePress\\Core\\Themes\\Shortcode\\Melange\\Lucid' => __DIR__ . '/../..' . '/src/Themes/Shortcode/Melange/Lucid.php',
        'ProfilePress\\Core\\Themes\\Shortcode\\PasswordResetThemeInterface' => __DIR__ . '/../..' . '/src/Themes/Shortcode/PasswordResetThemeInterface.php',
        'ProfilePress\\Core\\Themes\\Shortcode\\Passwordreset\\Fzbuk' => __DIR__ . '/../..' . '/src/Themes/Shortcode/Passwordreset/Fzbuk.php',
        'ProfilePress\\Core\\Themes\\Shortcode\\Passwordreset\\Perfecto' => __DIR__ . '/../..' . '/src/Themes/Shortcode/Passwordreset/Perfecto.php',
        'ProfilePress\\Core\\Themes\\Shortcode\\RegistrationThemeInterface' => __DIR__ . '/../..' . '/src/Themes/Shortcode/RegistrationThemeInterface.php',
        'ProfilePress\\Core\\Themes\\Shortcode\\Registration\\Boson' => __DIR__ . '/../..' . '/src/Themes/Shortcode/Registration/Boson.php',
        'ProfilePress\\Core\\Themes\\Shortcode\\Registration\\Fzbuk' => __DIR__ . '/../..' . '/src/Themes/Shortcode/Registration/Fzbuk.php',
        'ProfilePress\\Core\\Themes\\Shortcode\\Registration\\PerfectoLite' => __DIR__ . '/../..' . '/src/Themes/Shortcode/Registration/PerfectoLite.php',
        'ProfilePress\\Core\\Themes\\Shortcode\\ThemeInterface' => __DIR__ . '/../..' . '/src/Themes/Shortcode/ThemeInterface.php',
        'ProfilePress\\Core\\Themes\\Shortcode\\ThemesRepository' => __DIR__ . '/../..' . '/src/Themes/Shortcode/ThemesRepository.php',
        'ProfilePress\\Core\\Themes\\Shortcode\\Userprofile\\Daisy' => __DIR__ . '/../..' . '/src/Themes/Shortcode/Userprofile/Daisy.php',
        'ProfilePress\\Core\\Themes\\Shortcode\\Userprofile\\Dixon' => __DIR__ . '/../..' . '/src/Themes/Shortcode/Userprofile/Dixon.php',
        'ProfilePress\\Core\\Widgets\\Form' => __DIR__ . '/../..' . '/src/Widgets/Form.php',
        'ProfilePress\\Core\\Widgets\\Init' => __DIR__ . '/../..' . '/src/Widgets/Init.php',
        'ProfilePress\\Core\\Widgets\\TabbedWidget' => __DIR__ . '/../..' . '/src/Widgets/TabbedWidget.php',
        'ProfilePress\\Core\\Widgets\\TabbedWidgetDependency' => __DIR__ . '/../..' . '/src/Widgets/TabbedWidgetDependency.php',
        'ProfilePress\\Core\\Widgets\\UserPanel' => __DIR__ . '/../..' . '/src/Widgets/UserPanel.php',
        'Symfony\\Component\\CssSelector\\CssSelectorConverter' => __DIR__ . '/..' . '/symfony/css-selector/CssSelectorConverter.php',
        'Symfony\\Component\\CssSelector\\Exception\\ExceptionInterface' => __DIR__ . '/..' . '/symfony/css-selector/Exception/ExceptionInterface.php',
        'Symfony\\Component\\CssSelector\\Exception\\ExpressionErrorException' => __DIR__ . '/..' . '/symfony/css-selector/Exception/ExpressionErrorException.php',
        'Symfony\\Component\\CssSelector\\Exception\\InternalErrorException' => __DIR__ . '/..' . '/symfony/css-selector/Exception/InternalErrorException.php',
        'Symfony\\Component\\CssSelector\\Exception\\ParseException' => __DIR__ . '/..' . '/symfony/css-selector/Exception/ParseException.php',
        'Symfony\\Component\\CssSelector\\Exception\\SyntaxErrorException' => __DIR__ . '/..' . '/symfony/css-selector/Exception/SyntaxErrorException.php',
        'Symfony\\Component\\CssSelector\\Node\\AbstractNode' => __DIR__ . '/..' . '/symfony/css-selector/Node/AbstractNode.php',
        'Symfony\\Component\\CssSelector\\Node\\AttributeNode' => __DIR__ . '/..' . '/symfony/css-selector/Node/AttributeNode.php',
        'Symfony\\Component\\CssSelector\\Node\\ClassNode' => __DIR__ . '/..' . '/symfony/css-selector/Node/ClassNode.php',
        'Symfony\\Component\\CssSelector\\Node\\CombinedSelectorNode' => __DIR__ . '/..' . '/symfony/css-selector/Node/CombinedSelectorNode.php',
        'Symfony\\Component\\CssSelector\\Node\\ElementNode' => __DIR__ . '/..' . '/symfony/css-selector/Node/ElementNode.php',
        'Symfony\\Component\\CssSelector\\Node\\FunctionNode' => __DIR__ . '/..' . '/symfony/css-selector/Node/FunctionNode.php',
        'Symfony\\Component\\CssSelector\\Node\\HashNode' => __DIR__ . '/..' . '/symfony/css-selector/Node/HashNode.php',
        'Symfony\\Component\\CssSelector\\Node\\NegationNode' => __DIR__ . '/..' . '/symfony/css-selector/Node/NegationNode.php',
        'Symfony\\Component\\CssSelector\\Node\\NodeInterface' => __DIR__ . '/..' . '/symfony/css-selector/Node/NodeInterface.php',
        'Symfony\\Component\\CssSelector\\Node\\PseudoNode' => __DIR__ . '/..' . '/symfony/css-selector/Node/PseudoNode.php',
        'Symfony\\Component\\CssSelector\\Node\\SelectorNode' => __DIR__ . '/..' . '/symfony/css-selector/Node/SelectorNode.php',
        'Symfony\\Component\\CssSelector\\Node\\Specificity' => __DIR__ . '/..' . '/symfony/css-selector/Node/Specificity.php',
        'Symfony\\Component\\CssSelector\\Parser\\Handler\\CommentHandler' => __DIR__ . '/..' . '/symfony/css-selector/Parser/Handler/CommentHandler.php',
        'Symfony\\Component\\CssSelector\\Parser\\Handler\\HandlerInterface' => __DIR__ . '/..' . '/symfony/css-selector/Parser/Handler/HandlerInterface.php',
        'Symfony\\Component\\CssSelector\\Parser\\Handler\\HashHandler' => __DIR__ . '/..' . '/symfony/css-selector/Parser/Handler/HashHandler.php',
        'Symfony\\Component\\CssSelector\\Parser\\Handler\\IdentifierHandler' => __DIR__ . '/..' . '/symfony/css-selector/Parser/Handler/IdentifierHandler.php',
        'Symfony\\Component\\CssSelector\\Parser\\Handler\\NumberHandler' => __DIR__ . '/..' . '/symfony/css-selector/Parser/Handler/NumberHandler.php',
        'Symfony\\Component\\CssSelector\\Parser\\Handler\\StringHandler' => __DIR__ . '/..' . '/symfony/css-selector/Parser/Handler/StringHandler.php',
        'Symfony\\Component\\CssSelector\\Parser\\Handler\\WhitespaceHandler' => __DIR__ . '/..' . '/symfony/css-selector/Parser/Handler/WhitespaceHandler.php',
        'Symfony\\Component\\CssSelector\\Parser\\Parser' => __DIR__ . '/..' . '/symfony/css-selector/Parser/Parser.php',
        'Symfony\\Component\\CssSelector\\Parser\\ParserInterface' => __DIR__ . '/..' . '/symfony/css-selector/Parser/ParserInterface.php',
        'Symfony\\Component\\CssSelector\\Parser\\Reader' => __DIR__ . '/..' . '/symfony/css-selector/Parser/Reader.php',
        'Symfony\\Component\\CssSelector\\Parser\\Shortcut\\ClassParser' => __DIR__ . '/..' . '/symfony/css-selector/Parser/Shortcut/ClassParser.php',
        'Symfony\\Component\\CssSelector\\Parser\\Shortcut\\ElementParser' => __DIR__ . '/..' . '/symfony/css-selector/Parser/Shortcut/ElementParser.php',
        'Symfony\\Component\\CssSelector\\Parser\\Shortcut\\EmptyStringParser' => __DIR__ . '/..' . '/symfony/css-selector/Parser/Shortcut/EmptyStringParser.php',
        'Symfony\\Component\\CssSelector\\Parser\\Shortcut\\HashParser' => __DIR__ . '/..' . '/symfony/css-selector/Parser/Shortcut/HashParser.php',
        'Symfony\\Component\\CssSelector\\Parser\\Token' => __DIR__ . '/..' . '/symfony/css-selector/Parser/Token.php',
        'Symfony\\Component\\CssSelector\\Parser\\TokenStream' => __DIR__ . '/..' . '/symfony/css-selector/Parser/TokenStream.php',
        'Symfony\\Component\\CssSelector\\Parser\\Tokenizer\\Tokenizer' => __DIR__ . '/..' . '/symfony/css-selector/Parser/Tokenizer/Tokenizer.php',
        'Symfony\\Component\\CssSelector\\Parser\\Tokenizer\\TokenizerEscaping' => __DIR__ . '/..' . '/symfony/css-selector/Parser/Tokenizer/TokenizerEscaping.php',
        'Symfony\\Component\\CssSelector\\Parser\\Tokenizer\\TokenizerPatterns' => __DIR__ . '/..' . '/symfony/css-selector/Parser/Tokenizer/TokenizerPatterns.php',
        'Symfony\\Component\\CssSelector\\XPath\\Extension\\AbstractExtension' => __DIR__ . '/..' . '/symfony/css-selector/XPath/Extension/AbstractExtension.php',
        'Symfony\\Component\\CssSelector\\XPath\\Extension\\AttributeMatchingExtension' => __DIR__ . '/..' . '/symfony/css-selector/XPath/Extension/AttributeMatchingExtension.php',
        'Symfony\\Component\\CssSelector\\XPath\\Extension\\CombinationExtension' => __DIR__ . '/..' . '/symfony/css-selector/XPath/Extension/CombinationExtension.php',
        'Symfony\\Component\\CssSelector\\XPath\\Extension\\ExtensionInterface' => __DIR__ . '/..' . '/symfony/css-selector/XPath/Extension/ExtensionInterface.php',
        'Symfony\\Component\\CssSelector\\XPath\\Extension\\FunctionExtension' => __DIR__ . '/..' . '/symfony/css-selector/XPath/Extension/FunctionExtension.php',
        'Symfony\\Component\\CssSelector\\XPath\\Extension\\HtmlExtension' => __DIR__ . '/..' . '/symfony/css-selector/XPath/Extension/HtmlExtension.php',
        'Symfony\\Component\\CssSelector\\XPath\\Extension\\NodeExtension' => __DIR__ . '/..' . '/symfony/css-selector/XPath/Extension/NodeExtension.php',
        'Symfony\\Component\\CssSelector\\XPath\\Extension\\PseudoClassExtension' => __DIR__ . '/..' . '/symfony/css-selector/XPath/Extension/PseudoClassExtension.php',
        'Symfony\\Component\\CssSelector\\XPath\\Translator' => __DIR__ . '/..' . '/symfony/css-selector/XPath/Translator.php',
        'Symfony\\Component\\CssSelector\\XPath\\TranslatorInterface' => __DIR__ . '/..' . '/symfony/css-selector/XPath/TranslatorInterface.php',
        'Symfony\\Component\\CssSelector\\XPath\\XPathExpr' => __DIR__ . '/..' . '/symfony/css-selector/XPath/XPathExpr.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInitb8ccebde2f0f0c5c17c736d9547cc8a5::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitb8ccebde2f0f0c5c17c736d9547cc8a5::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInitb8ccebde2f0f0c5c17c736d9547cc8a5::$classMap;

        }, null, ClassLoader::class);
    }
}
