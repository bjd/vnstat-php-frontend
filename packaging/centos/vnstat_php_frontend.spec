# $Id$
# Authority: havard 
# Upstream: Bjorge Dijkstra <bjd,jooz.net> 

Summary: vnstat PHP frontend - network traffic monitor
Name: vnstat_php_frontend
Version: 1.5.1
Release: 1%{?dist}
License: GPL
Group: Applications/System
URL: http://www.sqweek.com/sqweek/index.php?p=1

Source: http://www.sqweek.com/sqweek/files/vnstat_php_frontend-%{version}.tar.gz
BuildRoot: %{_tmppath}/%{name}-%{version}-%{release}-root

BuildArch: noarch

#Requires: php-xxxxx>= x.x.x
#%{?el5:Requires: php-xxxx}
Requires: webserver vnstat php php-gd
Obsoletes: vnstat_php_frontend <= %{version}-%{release} 
Provides: vnstat_php_frontend = %{version}-%{release}

%description
 
This is a PHP fronted end to vnStat, a network traffic logger.  vnStat is console 
mode only. This script  makes a 'nice' report of the data collected by vnStat. 

vnstat is a network traffic monitor that keeps a log of daily network
traffic for the selected interface(s). vnstat is not a packet sniffer.
http://humdi.net/vnstat 
 
%prep

%setup -q -n %{name}-%{version} 

#Todo: get setting for vnstat-php.conf
#  $iface_list from vnstat.sysconfig
#  $data_dir to /var/lib/vnstat/ or from /etc/vnstat.conf

%{__cat} <<EOF >vnstat-php.conf
#
#  %{summary}
#

Alias /vnstat %{_datadir}/%{name}

<Directory "%{_datadir}/%{name}">
  Order Deny,Allow
  Deny from all
  Allow from 127.0.0.1
  # Set the default handler.
  DirectoryIndex index.php
  #Allow from all
</Directory>
EOF


%build

%install
%{__rm} -rf %{buildroot}
%{__mkdir} -p %{buildroot}/%{_datadir}/%{name}
%{__mkdir} -p %{buildroot}/%{_sysconfdir}/httpd/conf.d/

%{__install} -d -m0755 %{buildroot}%{_datadir}/%{name}/
%{__cp} -av *.{php,ttf} %{buildroot}%{_datadir}/%{name}/
%{__cp} -av lang/ themes/ %{buildroot}%{_datadir}/%{name}/

%{__install} -Dp -m0644 config.php %{buildroot}%{_datadir}/%{name}/config.php
#%{__install} -Dp -m0644 packaging/centos/vnstat-php.conf %{buildroot}%{_sysconfdir}/httpd/conf.d/vnstat-php.conf
%{__install} -Dp -m0644 vnstat-php.conf %{buildroot}%{_sysconfdir}/httpd/conf.d/vnstat-php.conf

# note
HOST=`hostname -f`
echo ""
echo "Next steps: "
echo ""
echo "[config apache access]"
echo " Use a editor and open this file:"
echo " /etc/httpd/conf.d/vnstat.conf"
echo ""
echo "[httpd services]"
echo " Restart httpd 'service httpd restart'"
echo ""
echo "[have fun.. ]"
echo " Use a webbrowser and open this link:"
echo " http://$HOST/vnstat"
echo ""
echo ""

%clean
%{__rm} -rf %{buildroot}

%files
%defattr(-, root, root, 0755)
%doc COPYING README vera_copyright.txt
%config(noreplace) %{_sysconfdir}/httpd/conf.d/vnstat-php.conf

%defattr(0640, root, apache, 0755)
%config(noreplace) %{_datadir}/%{name}/config.php

%defattr(0755, nobody, nobody, 0755)
%{_datadir}/%{name}/


%changelog
* Sun Mar 20 2011 Havard Sorli <havard@sorli.no> - 1.5.1
- Initial package. 
- spec file based on: http://svn.rpmforge.net/svn/trunk/rpms/vnstat/vnstat.spec
  http://svn.rpmforge.net/svn/trunk/rpms/phpmyadmin/phpmyadmin.spec
