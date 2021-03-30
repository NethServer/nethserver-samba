Summary: Nethserver Samba file server
Name: nethserver-samba
Version: 4.6.0
Release: 1%{?dist}
License: GPL
Source: %{name}-%{version}.tar.gz
# Build Source1 by executing prep-sources
Source1: %{name}-ui.tar.gz
BuildArch: noarch
URL: %{url_prefix}/%{name}
Provides: nethserver-ibays
Obsoletes: nethserver-ibays
Obsoletes: sssd-libwbclient
Conflicts: sssd-libwbclient

Requires: samba
Requires: tdb-tools
Requires: nethserver-ibays
Requires: samba-winbind

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
sed -i 's/_RELEASE_/%{version}/' %{name}.json

%install
rm -rf %{buildroot}
mkdir -p %{buildroot}/usr/share/cockpit/%{name}/
mkdir -p %{buildroot}/usr/share/cockpit/nethserver/applications/
mkdir -p %{buildroot}/usr/libexec/nethserver/api/%{name}/
tar xf %{SOURCE1} -C %{buildroot}/usr/share/cockpit/%{name}/
cp -a %{name}.json %{buildroot}/usr/share/cockpit/nethserver/applications/
cp -a api/* %{buildroot}/usr/libexec/nethserver/api/%{name}/

(cd root   ; find . -depth -print | cpio -dump %{buildroot})
%{genfilelist} %{buildroot} \
    --file /etc/sudoers.d/50_nsapi_nethserver_samba 'attr(0440,root,root)' \
    > %{name}-%{version}-filelist

mkdir -p %{buildroot}/%{_nsstatedir}/print_driver
mkdir -p %{buildroot}/%{_nsstatedir}/ibay

%files -f %{name}-%{version}-filelist
%doc COPYING
%defattr(-,root,root)
%dir %{_nseventsdir}/%{name}-update
%dir %attr(0755,root,root) %{_nsstatedir}/print_driver
%dir %attr(0775,root,root) %{_nsstatedir}/ibay
/usr/libexec/nethserver/api/%{name}/
%attr(0440,root,root) /etc/sudoers.d/50_nsapi_nethserver_samba

%changelog
* Tue Mar 30 2021 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 4.6.0-1
- Sharefolders: Disable shares - NethServer/dev#6468

* Fri Jan 29 2021 Davide Principi <davide.principi@nethesis.it> - 4.5.6-1
- Posix default ACL ignored by Reset - Bug NethServer/dev#6406

* Thu Jul 02 2020 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 4.5.5-1
- Human readable numbers in Cockpit dashboards - NethServer/dev#6206

* Wed Apr 29 2020 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 4.5.4-1
- Cannot list Samba shares with accented character - Bug NethServer/dev#6128

* Wed Jan 08 2020 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 4.5.3-1
- Cockpit: change package Dashboard page title - NethServer/dev#6004

* Thu Nov 21 2019 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 4.5.2-1
- Email: domain menu not correctly shown - Bug Nethserver/dev#5942

* Fri Nov 15 2019 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 4.5.1-1
- Cockpit file server: InheritOwner option value is ignored - Bug Nethserver/dev#5910

* Mon Oct 28 2019 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 4.5.0-1
- SambaAudit: improve perfomance - Nethserver/dev#5881
- Logs page in Cockpit - Bug NethServer/dev#5866

* Tue Oct 01 2019 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 4.4.0-1
- Sudoers based authorizations for Cockpit UI - NethServer/dev#5805

* Tue Sep 03 2019 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 4.3.4-1
- Cockpit. List correct application version - Nethserver/dev#5819

* Mon Aug 26 2019 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 4.3.3-1
- Cockpit. fix various bugs - Bug Nethserver/dev#5810
- Cockpit: wrong shared folder size - NethServer/dev#5802

* Wed Aug 07 2019 Davide Principi <davide.principi@nethesis.it> - 4.3.2-1
- Samba shares unaccessible after Samba updates - Bug NethServer/dev#5801

* Wed May 29 2019 Davide Principi <davide.principi@nethesis.it> - 4.3.1-1
- Fix sharedfolders Cockpit API executable bit -- NethServer/dev#5754

* Tue May 28 2019 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 4.3.0-1
- File server Cockpit UI - NethServer/dev#5754
- Cannot access shared folder after ns6 WS upgrade - Bug NethServer/dev#5767

* Mon Dec 17 2018 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 4.2.1-1
- Cannot access shared folders after ns6 config restore - Bug NethServer/dev#5673

* Mon Dec 03 2018 Davide Principi <davide.principi@nethesis.it> - 4.2.0-1
- Samba: can't access shares - Bug NethServer/dev#5647

* Wed May 16 2018 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 4.1.0-1
- Change of defaults for NS 7.5 - NethServer/dev#5490

* Thu Mar 29 2018 Davide Principi <davide.principi@nethesis.it> - 4.0.1-1
- Bad shared folder DB props after ns6upgrade - Bug NethServer/dev#5439

* Wed Jan 10 2018 Davide Principi <davide.principi@nethesis.it> - 4.0.0-1
- Windows file server page - NethServer/dev#5404
- Merged with nethserver-ibays-3.1.1

* Mon Sep 18 2017 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 2.0.10-1
- Read-only filesystem with kernel 3.10.0-693 - Bug NethServer/dev#5349

* Thu Jul 06 2017 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 2.0.8-1
- Smbd do not listen additional IP - Bug NethServer/dev#5319

* Thu May 25 2017 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 2.0.7-1
- Migrate LogonDrive prop - Bug NethServer/dev#5290

* Wed May 10 2017 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 3.1.1-1
- sf: Upgrade from NS 6 via backup and restore - NethServer/dev#5234

* Thu Apr 20 2017 Davide Principi <davide.principi@nethesis.it> - 2.0.6-1
- Accounts provider guided configuration - NethServer/dev#5253
- Upgrade from NS 6 via backup and restore - NethServer/dev#5234

* Tue Apr 04 2017 Davide Principi <davide.principi@nethesis.it> - 3.1.0-1
- sf: Allow capital letters in shared folder name - NethServer/dev#5247
- sf: Upgrade from NS 6 via backup and restore - NethServer/dev#5234

* Mon Mar 06 2017 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 3.0.4-1
- sf: Migration from sme8 - NethServer/dev#5196

* Mon Mar 06 2017 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 2.0.5-1
- Migration from sme8 - NethServer/dev#5196

* Mon Jan 16 2017 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 2.0.4-1
- Exhibit bad network configuration - NethServer/dev#5193
- DC: restore configuration fails - NethServer/dev#5188

* Thu Jan 12 2017 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 3.0.3-1
- sf: Shared Folder ACL applied to a group sometimes not respected - Bug NethServer/dev#5186

* Fri Dec 09 2016 Davide Principi <davide.principi@nethesis.it> - 2.0.3-1
- Revert Samba config to support NTLM auth by default - Bug NethServer/dev#5160

* Mon Nov 07 2016 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 3.0.2-1
- sf: Guest cannot write to shared folder - Bug NethServer/dev#5141

* Mon Nov 07 2016 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 2.0.2-1
- SMB filesystem ACLs are not applied - Bug NethServer/dev#5142
- Shared folders: trash doesn't exclude tmp file - Bug #3422

* Wed Sep 28 2016 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 3.0.1-1
- sf: Shared folder listing denied with special ACL - Bug NethServer/dev#5111

* Thu Sep 01 2016 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 2.0.1-1
- Could not connect my Samba home share - Bug NethServer/dev#5090

* Thu Jul 07 2016 Stefano Fancello <stefano.fancello@nethesis.it> - 3.0.0-1
- sf: First NS7 release

* Thu Jul 07 2016 Stefano Fancello <stefano.fancello@nethesis.it> - 2.0.0-1
- First NS7 release
