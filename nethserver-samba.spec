Summary: Nethserver specific Samba configuration files and templates
Name: nethserver-samba
Version: 2.0.7
Release: 1%{?dist}
License: GPL
Source: %{name}-%{version}.tar.gz
BuildArch: noarch
URL: %{url_prefix}/%{name} 

Requires: samba
Requires: tdb-tools
Requires: nethserver-ibays
Requires: sssd-libwbclient

BuildRequires: nethserver-devtools

%description
* Provides SMB shares as Shared folders (ibays)
* CUPS support
* Active Directory GSSAPI/Kerberos authentication

%prep
%setup

%build
%{makedocs}
mkdir -p root%{perl_vendorlib}
perl createlinks

%install
rm -rf %{buildroot}
(cd root   ; find . -depth -print | cpio -dump %{buildroot})
%{genfilelist} %{buildroot} > %{name}-%{version}-filelist

mkdir -p %{buildroot}/%{_nsstatedir}/print_driver

%files -f %{name}-%{version}-filelist
%doc COPYING
%defattr(-,root,root)
%dir %{_nseventsdir}/%{name}-update
%dir %attr(0755,root,root) %{_nsstatedir}/print_driver

%changelog
* Thu May 25 2017 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 2.0.7-1
- Migrate LogonDrive prop - Bug NethServer/dev#5290

* Thu Apr 20 2017 Davide Principi <davide.principi@nethesis.it> - 2.0.6-1
- Accounts provider guided configuration - NethServer/dev#5253 
- Upgrade from NS 6 via backup and restore - NethServer/dev#5234 

* Mon Mar 06 2017 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 2.0.5-1
- Migration from sme8 - NethServer/dev#5196

* Mon Jan 16 2017 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 2.0.4-1
- Exhibit bad network configuration - NethServer/dev#5193
- DC: restore configuration fails - NethServer/dev#5188

* Fri Dec 09 2016 Davide Principi <davide.principi@nethesis.it> - 2.0.3-1
- Revert Samba config to support NTLM auth by default - Bug NethServer/dev#5160

* Mon Nov 07 2016 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 2.0.2-1
- SMB filesystem ACLs are not applied - Bug NethServer/dev#5142
- Shared folders: trash doesn't exclude tmp file - Bug #3422

* Thu Sep 01 2016 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 2.0.1-1
- Could not connect my Samba home share - Bug NethServer/dev#5090

* Thu Jul 07 2016 Stefano Fancello <stefano.fancello@nethesis.it> - 2.0.0-1
- First NS7 release

* Wed Apr 27 2016 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 1.5.5-1
- Samba recycle bin migration - Bug #3374 [NethServer]

* Wed Dec 02 2015 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 1.5.4-1
- Roaming profile doesn't  work with W8,W8.1,W10 - Bug #3277 [NethServer]
- Hide samba share - Feature #3208 [NethServer]

* Tue Sep 29 2015 Davide Principi <davide.principi@nethesis.it> - 1.5.3-1
- Make Italian language pack optional - Enhancement #3265 [NethServer]
- Event trusted-networks-modify - Enhancement #3195 [NethServer]

* Wed Jul 15 2015 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 1.5.2-1
- Event trusted-networks-modify - Enhancement #3195 [NethServer]

* Thu Apr 23 2015 Davide Principi <davide.principi@nethesis.it> - 1.5.1-1
- Language packs support - Feature #3115 [NethServer]

* Tue Mar 10 2015 Davide Principi <davide.principi@nethesis.it> - 1.5.0-1
- Differentiate root and admin users - Feature #3026 [NethServer]

* Tue Feb 10 2015 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 1.4.11-1.ns6
- WINS registrations flood log.nmbd - Bug #3013 [NethServer]

* Tue Jan 20 2015 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 1.4.10-1.ns6
- root user: nethserver-samba-password-modify error - Bug #2968 [NethServer]

* Thu Dec 11 2014 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 1.4.9-1.ns6
- Samba: error "GROUP is a Domain, not a group" - Bug #2976 [NethServer]

* Tue Dec 09 2014 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 1.4.8-1.ns6
- Samba: error "USER is a Domain, not a user" - Bug #2951 [NethServer]
- Change dependency to Samba < 4.0 -  Refs #2955 [NethServer]

* Tue Nov 11 2014 Davide Principi <davide.principi@nethesis.it> - 1.4.7-1.ns6
- Samba access denied from second green network - Bug #2925 [NethServer]

* Tue Sep 16 2014 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 1.4.6-1.ns6
- Fix: Samba machine accounts not migrated - Bug #2832
- Fix: Can't access group shared folder (samba WS mode) - Bug #2815 
- Fix: Smb logon drive ignored during migration - Bug #2811 
- Fix: Samba SAM db migration fails on group_mapping.ldb - Bug #2806 
- Samba domain SID deep checking - Enhancement #2840
- Edit workgroup name when role is Workstation - Enhancement #2803 
- Samba: map local users to Domain Users - Enhancement #2792 

* Thu Jul 03 2014 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 1.4.5-1.ns6
- Fix: Login to Samba fails after migration - Bug #2780

* Thu Jun 19 2014 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 1.4.4-1.ns6
- Fix bug #2733 - Domain Administrators rights not enforced by workstations.
- Decrease default Samba log verbosity #2747

* Thu Jun 12 2014 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 1.4.3-1.ns6
- Configurable AD accounts LDAP subtree - Enhancement #2727
- Keep changes to netlogon.bat - Enhancement #2725
- Backup config: minimize creation of new backup - Enhancement #2699

* Wed Feb 26 2014 Davide Principi <davide.principi@nethesis.it> - 1.4.2-1.ns6
- Revamp web UI style - Enhancement #2656 [NethServer]
- Samba: backup secrets.tdb in config backup - Enhancement #2653 [NethServer]
- Implement hostname-modify event for samba  - Enhancement #2626 [NethServer]

* Wed Feb 05 2014 Davide Principi <davide.principi@nethesis.it> - 1.4.1-1.ns6
- RST format for help files - Enhancement #2627 [NethServer]
- Move admin user in LDAP DB - Feature #2492 [NethServer]
- Update all inline help documentation - Task #1780 [NethServer]

* Wed Dec 18 2013 Davide Principi <davide.principi@nethesis.it> - 1.4.0-1.ns6
- Kerberos keytab file is missing for new services - Bug #2407 [NethServer]
- Directory: backup service accounts passwords  - Enhancement #2063 [NethServer]
- Service supervision with Upstart - Feature #2014 [NethServer]

* Tue Jul 16 2013 Davide Principi <davide.principi@nethesis.it> - 1.3.6-1.ns6
- Yum transaction aborted while installing nethserver-file-server group - Bug #2041 [Nethgui]

* Thu Jul 04 2013 Davide Principi <davide.principi@nethesis.it> - 1.3.5-1.ns6
- Fixed "value is not boolean!" warning - Bug #2039 [NethServer]

* Mon Jul 01 2013 Davide Principi <davide.principi@nethesis.it> - 1.3.4-1.ns6
- File permissions not inherited when POSIX ACL is present - Bug #2039 [NethServer]
- Share connection error NT_STATUS_ACCESS_DENIED  - Bug #1997 [NethServer]

* Mon Jun 17 2013 Davide Principi <davide.principi@nethesis.it> - 1.3.3-1.ns6
- Added a 5% of tolerance to deadline time, to avoid ticket expiration during renewal - Feature #1746 [NethServer]

* Mon Jun 10 2013 Davide Principi <davide.principi@nethesis.it> - 1.3.2-1.ns6
- Disable idmap nss backend if ServerRole is ADS #1997

* Mon Jun 10 2013 Davide Principi <davide.principi@nethesis.it> - 1.3.1-1.ns6
- Fix samba share connection error NT_STATUS_ACCESS_DENIED #1997

* Wed May 29 2013 Davide Principi <davide.principi@nethesis.it> - 1.3.0-1.ns6
- Active Directory member role. Refs #1746
- *.spec.in: Require cyrus-sasl-gssapi, krb5-workstation, perl-Authen-Krb5

* Tue May  7 2013 Davide Principi <davide.principi@nethesis.it> - 1.2.1-1.ns6
- nethserver-samba-group-sync action: check if samba idmap backend is functional before going on, and don't fail if a unix group does not exist #1870

* Tue Apr 30 2013 Davide Principi <davide.principi@nethesis.it> - 1.2.0-1.ns6
- Store machine accounts under ou=Computers branch #1890 
- Implant migrated SID as "localsid" #1895
- Shared folder guest access #1882
- winregistry-patches served by httpd-admin #1799
- SharedFolder UI plugin: hide profile widget #1881
- Full automatic package install/upgrade/uninstall support #1870 #1872 #1874
- Update group description when changed from UI #1861
- Migration ibay profile #1855
- Fixed idmap LDAP backend configuration: pre-defined groups were not available after migration #1829
- Partial implementation of Active Directory domain join #1746
- Removed shadow copy checkbox from UI #1750

* Tue Mar 19 2013 Davide Principi <davide.principi@nethesis.it> - 1.1.0-1.ns6
- smb.conf template: implement ibay profiles. Refs #1724
- /etc/fstab template: enable acl & xattrs attributes. #1658
- smb WinsServerStatus WinsServerIP props to configure wins support. #7
- smb.conf template refactor. #7
- CUPS integration. #1626
- Migration support. #1657 #1667
- Protect samba password ldap field. #1650
- *.spec.in: Removed nethserver-devtools specific version requirement; Fixed Released tag expansion Refs; use url_prefix macro in URL tag; set minimum version requirements. #1653 #1654
 


