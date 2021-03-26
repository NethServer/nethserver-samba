#!/usr/bin/perl

#
# Copyright (C) 2019 Nethesis S.r.l.
# http://www.nethesis.it - nethserver@nethesis.it
#
# This script is part of NethServer.
#
# NethServer is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 3 of the License,
# or any later version.
#
# NethServer is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with NethServer.  If not, see COPYING.
#

use strict;
use warnings;
use NethServer::SSSD;

# HACK for variable visibility: make sure to not reuse such names!
my %folders;
my $record;       # points to a hash of element contents
my $context = ''; # name of current element

sub disk_usage
{
    # file count
    my @duc_count_output = `/usr/bin/duc ls --count -b -n -d /var/cache/duc/duc.db /var/lib/nethserver/ibay 2>/dev/null`;
    if (scalar @duc_count_output > 0) {
        my $ibay_name = '';
        my $count = '';
        my $size ='';

        for(@duc_count_output){
            $_ =~ m/([0-9]+) (.*)$/;
            $count = $1;
            $ibay_name = $2;
            $folders{$ibay_name}{'files'} = $count + 0;
        }

        # ibay size
        my @duc_size_output = `/usr/bin/duc ls -b -n -d /var/cache/duc/duc.db /var/lib/nethserver/ibay 2>/dev/null`;
        if (scalar @duc_size_output > 0) {
            for(@duc_size_output){
                $_ =~ m/([0-9]+) (.*)$/;
                $size = $1;
                $ibay_name = $2;
                $folders{$ibay_name}{'size'} = $size + 0;
            }
        }
    }
    return \%folders;

}

sub _prepareAcl
{
    my $acls = shift;
    my $re = shift;
    my @alist = ();
    foreach(keys %{$acls}) {
        if($_ !~ m/^(GOWNER|EVERYONE)$/ && $acls->{$_} =~ $re) {
            push @alist, $_;
        }
    }
    @alist = sort(@alist);
    return join(',', @alist);
}

sub item2props
{
    my $item = shift;

    my $acls = $item->{'acls'};
    my $isAd = NethServer::SSSD->new()->isAD();

    my %props = (
        'Description' => $item->{'Description'} || '',
        'SmbAuditStatus' => $item->{'SmbAuditStatus'} || 'disabled',
        'SmbRecycleBinStatus' => $item->{'SmbRecycleBinStatus'} || 'disabled',
        'SmbRecycleBinVersionsStatus' => $item->{'SmbRecycleBinVersionsStatus'} || 'disabled',
        'SmbShareBrowseable' => $item->{'SmbShareBrowseable'} || 'disabled',
        'status' => $item->{'status'} || 'enabled',
    );

    if($isAd) {
        %props = (%props,
            'AclRead' => _prepareAcl($acls, qr/r/),
            'AclWrite' => _prepareAcl($acls, qr/w/),
            'GroupAccess' => $acls->{'GOWNER'},
            'OtherAccess' => $acls->{'EVERYONE'} =~ s/w//r,
            'OwningGroup' => $item->{'OwningGroup'},
            'SmbGuestAccessType' => ($item->{'guestAccess'} eq 'enabled' && $acls->{'EVERYONE'}) ? $acls->{'EVERYONE'} : 'none',
        );
    } else {
        %props = (%props,
            'AclRead' => '',
            'AclWrite' => '',
            'GroupAccess' => '',
            'OtherAccess' => '',
            'OwningGroup' => '',
            'SmbGuestAccessType' => $acls->{'EVERYONE'} ? $acls->{'EVERYONE'} : 'rw',
        );
    }

    return %props;
}

1;
