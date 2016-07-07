Name: nethserver-ibays
Summary: Shared directories configuration
Version: 3.0.0
Release: 1%{?dist}
License: GPL
URL: %{url_prefix}/%{name} 
Source: %{name}-%{version}.tar.gz

BuildArch: noarch
Requires: nethserver-base
BuildRequires: perl, perl(File::Path), nethserver-devtools >= 1.1.0-4

%description 
Information-bays (ibays) are filesystem directories accessible through
services provided by other packages, such as Samba, Sftp, Http

%prep
%setup

%build
%{makedocs}
perl createlinks

%install
rm -rf %{buildroot}
(cd root   ; find . -depth -print | cpio -dump %{buildroot})
%{genfilelist} %{buildroot} > %{name}-%{version}-%{release}-filelist

mkdir -p %{buildroot}/%{_nsstatedir}/ibay

%files -f %{name}-%{version}-%{release}-filelist
%defattr(-,root,root)
%dir %{_nseventsdir}/%{name}-update
%doc COPYING
%dir %attr(0775,root,root) %{_nsstatedir}/ibay

%changelog
* Thu Jul 07 2016 Stefano Fancello <stefano.fancello@nethesis.it> - 3.0.0-1
- First NS7 release

* Tue Sep 29 2015 Davide Principi <davide.principi@nethesis.it> - 2.1.5-1
- Make Italian language pack optional - Enhancement #3265 [NethServer]

* Mon Jun 22 2015 Davide Principi <davide.principi@nethesis.it> - 2.1.4-1
- Create Shared Folder tab not reset - Bug #3193 [NethServer]

* Thu Apr 02 2015 Davide Principi <davide.principi@nethesis.it> - 2.1.3-1
- Increase default maxIbayNameLength - Enhancement #3090 [NethServer]

* Wed Oct 22 2014 Davide Principi <davide.principi@nethesis.it> - 2.1.2-1.ns6
- Ibay: acl dump not executed before backup - Bug #2912 [NethServer]

* Thu Oct 02 2014 Davide Principi <davide.principi@nethesis.it> - 2.1.1-1.ns6
- ACLs not restored - Bug #2860 [NethServer]

* Wed Aug 20 2014 Davide Principi <davide.principi@nethesis.it> - 2.1.0-1.ns6
- Backup config: minimize creation of new backup - Enhancement #2699 [NethServer]

* Wed Feb 26 2014 Davide Principi <davide.principi@nethesis.it> - 2.0.3-1.ns6
- Minor code cleanup

* Wed Feb 05 2014 Davide Principi <davide.principi@nethesis.it> - 2.0.2-1.ns6
- RST format for help files - Enhancement #2627 [NethServer]
- Ibay contents inherit default owner - Enhancement #2573 [NethServer]
- Move admin user in LDAP DB - Feature #2492 [NethServer]

* Wed Oct 16 2013 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 2.0.1-1.ns6
- Fix write permission for owning group #2156
- Backup ibay directory structure in backup-config #2043

* Tue Apr 30 2013 Davide Principi <davide.principi@nethesis.it> - 2.0.0-1.ns6
- ACLs template for system users and groups #1891
- Fixed application of permissions for owning group #1863
- Backup POSIX ACLs #1848

* Tue Mar 19 2013 Davide Principi <davide.principi@nethesis.it> - 1.1.0-1.ns6
- Raw Posix ACLs in AclList prop of ibay records. Refs #1736
- Migration support. Refs #1688
- *.spec.in: fixed URL and Release tags expansion, removed obsolete Group and BuildRoot tags

