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
use XML::Parser;
use NethServer::ApiTools;
use NethServer::SSSD;

# HACK for variable visibility: make sure to not reuse such names!
my %folders;
my $record;       # points to a hash of element contents
my $context = ''; # name of current element

sub disk_usage
{
    my $duc_output = `/usr/bin/duc xml -x -d /var/cache/duc/duc.db /var/lib/nethserver/ibay 2>/dev/null`;
    if ($duc_output) {
        my $parser = XML::Parser->new( Handlers => {
                Start =>   \&handle_elem_start,
                End =>   \&handle_elem_end
            });

        $parser->parse($duc_output);

        # save element name and attributes
        sub handle_elem_start {
            my( $expat, $name, %atts ) = @_;
            return if ($context eq 'ent'); # analize only first level
            $context = $name;
            return unless( $name eq 'ent' && $atts{'type'} eq 'dir');
            $record = { name => $atts{'name'}, size => int($atts{'size_actual'}), files => int($atts{'count'})} if( $name eq 'ent' );
        }


        # if this is an <ent>, collect all the data into a record
        sub handle_elem_end {
            my( $expat, $name ) = @_;
            return unless( $name eq 'ent' );
            if (scalar (keys %$record) > 0) {
                # skip non-existing ibays
                if (-d "/var/lib/nethserver/ibay/".$record->{'name'}) {
                    $folders{$record->{'name'}}{'size'} = $record->{'size'};
                    $folders{$record->{'name'}}{'files'} = $record->{'files'};
                }
            }
            $record = {};
            $context = '';
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
    );

    if($isAd) {
        %props = (%props,
            'AclRead' => _prepareAcl($acls, qr/r/),
            'AclWrite' => _prepareAcl($acls, qr/w/),
            'GroupAccess' => $acls->{'GOWNER'},
            'OtherAccess' => $acls->{'EVERYONE'} =~ s/w//r,
            'OwningGroup' => $item->{'OwningGroup'},
            'SmbGuestAccessType' => $acls->{'EVERYONE'} ? $acls->{'EVERYONE'} : 'none',
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
