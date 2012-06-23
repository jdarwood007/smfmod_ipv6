IPv6 support for SMF 2.0+ Only.  I will not backport these changes to 1.1.

This customization will add support for the IPv6 enabled clients in SMF 2.0.  Specifically posts, ip tracking and ban management have been changed.  Member search from the admin panel does support this by default.

Due to the size and necessity of the changes, please do not continue with installation if any change should fail the test.  This may lead to unexpected results or a unusable forum.

Note: During uninstallation, all IPv6 enabled bans will be disabled.  This customization makes attempts to track those changes in columns it adds to the ban_items table.  If you remove the database changes, this tracking will be lost.  In addition upon reinstallation this customization makes attempts to re-enable those bans that should still be active.

Note: Currently you are unable to link via iurl/url or auto linking ipv6 urls.  This is due to the square brackets that ipv6 addresses are enclosed in.  However this doesn't affect domains.

Thanks to [url=http://www.soucy.org]Ray Soucy[/url]'s for his IPv6 functions he wrote and allowing usage of them in this customization.

This customization is released under the BSD 3-Clause license. The terms of the license are including in this package (LICENSE.txt) or available on Simple Machines website at http://www.simplemachines.org/about/smf/license.php.

ChangeLog:

Version 1.0.1
! Fixed a search to use end instead of ?>

Version 1.0
! Release
