export USER=anuragh      # Replace username with the name for your new user
export HOST=cubet      # Replace hostname with the name for your host
export TZ="Asia/Kolkata" # Replace Europe/London with your Region/City
passwd && \
chsh -s /bin/zsh
echo "en_US.UTF-8 UTF-8" > /etc/locale.gen && \
locale-gen && \
echo "LANG=\"en_US.UTF-8\"" > /etc/locale.conf && \
echo "KEYMAP=us" > /etc/vconsole.conf && \
export LANG="en_US.UTF-8" && \
export LC_COLLATE="C"
ln -sf /usr/share/zoneinfo/$TZ /etc/localtime  && \
hwclock -uw # or hwclock --systohc --utc
echo $HOST > /etc/hostname
useradd -m -G  docker,input,kvm,libvirt,storage,video,wheel -s /bin/zsh $USER && \
passwd $USER && \
echo "$USER ALL=(ALL) NOPASSWD: ALL" >> /etc/sudoers && \
echo "Defaults timestamp_timeout=0" >> /etc/sudoers
cat << EOF >> /etc/hosts
# <ip-address>	<hostname.domain.org>	<hostname>
127.0.0.1	localhost
::1		localhost
127.0.1.1	$HOST.localdomain	$HOST
EOF
sed -i 's/BINARIES=()/BINARIES=("\/usr\/bin\/btrfs")/' /etc/mkinitcpio.conf && \
sed -i 's/MODULES=()/MODULES=(amdgpu)/' /etc/mkinitcpio.conf && \
sed -i 's/#COMPRESSION="lz4"/COMPRESSION="lz4"/' /etc/mkinitcpio.conf && \
sed -i 's/#COMPRESSION_OPTIONS=()/COMPRESSION_OPTIONS=(-9)/' /etc/mkinitcpio.conf && \
sed -i 's/^HOOKS.*/HOOKS=(base systemd autodetect modconf block sd-encrypt filesystems keyboard fsck)/' /etc/mkinitcpio.conf
# if you have more than 1 btrfs drive
# sed -i 's/^HOOKS.*/HOOKS=(base systemd autodetect modconf block sd-encrypt resume btrfs filesystems keyboard fsck)/' mkinitcpio.conf

mkinitcpio -p linux
echo "options snd_ac97_codec power_save=1" > /etc/modprobe.d/audio_powersave.conf
echo "options iwlwifi power_save=1" >> /etc/modprobe.d/iwlwifi.conf
echo "options iwlwifi uapsd_disable=0" >> /etc/modprobe.d/iwlwifi.conf
#echo 'ACTION=="add", SUBSYSTEM=="scsi_host", KERNEL=="host*", ATTR{link_power_management_policy}="med_power_with_dipm"' > /etc/udev/rules.d/hd_power_save.rules
# cat << EOF > /etc/tlp.conf
# SATA_LINKPWR_ON_AC="max_performance"
# SATA_LINKPWR_ON_BAT="med_power_with_dipm"
# RADEON_POWER_PROFILE_ON_AC="high"
# RADEON_POWER_PROFILE_ON_BAT="low"
# RESTORE_DEVICE_STATE_ON_STARTUP="1"
# EOF
cat << EOF >> /etc/NetworkManager/conf.d/nm.conf
[device]
wifi.backend=iwd
EOF
echo 'PRUNENAMES = ".snapshots"' >> /etc/updatedb.conf
cat << EOF > /etc/xdg/reflector/reflector.conf
# Set the output path where the mirrorlist will be saved (--save).
--save /etc/pacman.d/mirrorlist
# Select the transfer protocol (--protocol).
--protocol https
# Use only the  most recently synchronized mirrors (--latest).
--latest 100
# Sort the mirrors by MirrorStatus score
--sort score
EOF
mkdir /etc/pacman.d/hooks && cat << EOF > /etc/pacman.d/hooks/999-sign_kernel_for_secureboot.hook
[Trigger]
Operation = Install
Operation = Upgrade
Type = Package
Target = linux
Target = linux-lts
Target = linux-hardened
Target = linux-zen
Target = linux-xanmod
Target = linux-xanmod-cacule
Target = linux-xanmod-git
Target = linux-xanmod-lts
Target = linux-xanmod-rt
Target = linux-xanmod-anbox

[Action]
Description = Signing kernel with Machine Owner Key for Secure Boot
When = PostTransaction
Exec = /usr/bin/fd vmlinuz /boot -d 1 -x /usr/bin/sbsign --key /etc/refind.d/keys/refind_local.key --cert /etc/refind.d/keys/refind_local.crt --output {} {}
Depends = sbsigntools
Depends = fd
EOF
cat << EOF > /etc/pacman.d/hooks/refind.hook
[Trigger]
Operation=Upgrade
Type=Package
Target=refind

[Action]
Description = Updating rEFInd on ESP
When=PostTransaction
Exec=/usr/bin/refind-install --shim /usr/share/shim-signed/shimx64.efi --localkeys
EOF
cat << EOF > /etc/pacman.d/hooks/zsh.hook
[Trigger]
Operation = Install
Operation = Upgrade
Operation = Remove
Type = Path
Target = usr/bin/*
[Action]
Depends = zsh
When = PostTransaction
Exec = /usr/bin/install -Dm644 /dev/null /var/cache/zsh/pacman
EOF

cat << EOF > /etc/pacman.d/hooks/mirrorupgrade.hook
[Trigger]
Operation = Upgrade
Type = Package
Target = pacman-mirrorlist

[Action]
Description = Updating pacman-mirrorlist with reflector and removing pacnew...
When = PostTransaction
Depends = reflector
Exec = /bin/sh -c 'systemctl start reflector.service; if [ -f /etc/pacman.d/mirrorlist.pacnew ]; then rm /etc/pacman.d/mirrorlist.pacnew; fi'
EOF
cat << EOF > /etc/udev/rules.d/60-ioschedulers.rules
# set scheduler for NVMehttp://0.0.0.0:8000/
# set scheduler for rotating disks
ACTION=="add|change", KERNEL=="sd[a-z]", ATTR{queue/rotational}=="1", ATTR{queue/scheduler}="bfq"
EOF
cat << EOF > /etc/systemd/swap.conf
#  This file is part of systemd-swap.
#
# Entries in this file show the systemd-swap defaults as
# specified in /usr/share/systemd-swap/swap-default.conf
# You can change settings by editing this file.
# Defaults can be restored by simply deleting this file.
#
# See swap.conf(5) and /usr/share/systemd-swap/swap-default.conf for details.
zram_enabled=1
zswap_enabled=0
swapfc_enabled=0
zram_size=\$(( RAM_SIZE / 4 ))
EOF
sed -i 's/^CFLAGS.*/CFLAGS="-march=native -mtune=native -O2 -pipe -fstack-protector-strong --param=ssp-buffer-size=4 -fno-plt"/' /etc/makepkg.conf && \
sed -i 's/^CXXFLAGS.*/CXXFLAGS="-march=native -mtune=native -O2 -pipe -fstack-protector-strong --param=ssp-buffer-size=4 -fno-plt"/' /etc/makepkg.conf && \
sed -i 's/^#RUSTFLAGS.*/RUSTFLAGS="-C opt-level=2 -C target-cpu=native"/' /etc/makepkg.conf && \
sed -i 's/^#BUILDDIR.*/BUILDDIR=\/tmp\/makepkg/' /etc/makepkg.conf && \
sed -i 's/^#MAKEFLAGS.*/MAKEFLAGS="-j$(getconf _NPROCESSORS_ONLN) --quiet"/' /etc/makepkg.conf && \
sed -i 's/^COMPRESSGZ.*/COMPRESSGZ=(pigz -c -f -n)/' /etc/makepkg.conf && \
sed -i 's/^COMPRESSBZ2.*/COMPRESSBZ2=(pbzip2 -c -f)/' /etc/makepkg.conf && \
sed -i 's/^COMPRESSXZ.*/COMPRESSXZ=(xz -T "$(getconf _NPROCESSORS_ONLN)" -c -z --best -)/' /etc/makepkg.conf && \
sed -i 's/^COMPRESSZST.*/COMPRESSZST=(zstd -c -z -q --ultra -T0 -22 -)/' /etc/makepkg.conf && \
sed -i 's/^COMPRESSLZ.*/COMPRESSLZ=(lzip -c -f)/' /etc/makepkg.conf && \
sed -i 's/^COMPRESSLRZ.*/COMPRESSLRZ=(lrzip -9 -q)/' /etc/makepkg.conf && \
sed -i 's/^COMPRESSLZO.*/COMPRESSLZO=(lzop -q --best)/' /etc/makepkg.conf && \
sed -i 's/^COMPRESSZ.*/COMPRESSZ=(compress -c -f)/' /etc/makepkg.conf && \
sed -i 's/^COMPRESSLZ4.*/COMPRESSLZ4=(lz4 -q --best)/' /etc/makepkg.conf
sed -i 's/#UseSyslog/UseSyslog/' /etc/pacman.conf && \
sed -i 's/#Color/Color\\\nILoveCandy/' /etc/pacman.conf && \
sed -i 's/Color\\/Color/' /etc/pacman.conf && \
sed -i 's/#TotalDownload/TotalDownload/' /etc/pacman.conf && \
sed -i 's/#CheckSpace/CheckSpace/' /etc/pacman.conf
cat <<EOF > /etc/chrony.conf
# Use public NTP servers from the pool.ntp.org project.
server 0.pool.ntp.org offline
server 1.pool.ntp.org offline
server 2.pool.ntp.org offline
server 3.pool.ntp.org offline

# Record the rate at which the system clock gains/losses time.
driftfile /etc/chrony.drift

# In first three updates step the system clock instead of slew
# if the adjustment is larger than 1 second.
makestep 1.0 3

# Enable kernel synchronization of the real-time clock (RTC).
rtcsync

rtconutc
EOF
cat << EOF > /etc/NetworkManager/dispatcher.d/10-chrony
#!/bin/sh

INTERFACE=\$1
STATUS=\$2

# Make sure we're always getting the standard response strings
LANG='C'

CHRONY=\$(which chronyc)

chrony_cmd() {
    echo "Chrony going \$1."
    exec \$CHRONY -a \$1
}

nm_connected() {
    [ "\$(nmcli -t --fields STATE g)" = 'connected' ]
}

case "\$STATUS" in
    up)
        chrony_cmd online
    ;;
    vpn-up)
        chrony_cmd online
    ;;
    down)
        # Check for active interface, take offline if none is active
        nm_connected || chrony_cmd offline
    ;;
    vpn-down)
        # Check for active interface, take offline if none is active
        nm_connected || chrony_cmd offline
    ;;
EOF
chmod +x /etc/NetworkManager/dispatcher.d/10-chrony
mkdir /etc/docker && cat << EOF > /etc/docker/daemon.json
{
  "ipv6": true,
  "fixed-cidr-v6": "fd00::/80",
  "storage-driver": "btrfs"
}
EOF
sed -i 's/^umask.*/umask\ 077/' /etc/profile && \
chmod 700 /etc/{iptables,arptables,nftables.conf} && \
echo "auth optional pam_faildelay.so delay=4000000" >> /etc/pam.d/system-login && \
echo "tcp_bbr" > /etc/modules-load.d/bbr.conf && \
echo "write-cache" > /etc/apparmor/parser.conf
cat << EOF >/etc/sysctl.d/99-sysctl-performance-tweaks.conf
# The swappiness sysctl parameter represents the kernel's preference (or avoidance) of swap space. Swappiness can have a value between 0 and 100, the default value is 60. 
# A low value causes the kernel to avoid swapping, a higher value causes the kernel to try to use swap space. Using a low value on sufficient memory is known to improve responsiveness on many systems.
vm.swappiness=10

# The value controls the tendency of the kernel to reclaim the memory which is used for caching of directory and inode objects (VFS cache). 
# Lowering it from the default value of 100 makes the kernel less inclined to reclaim VFS cache (do not set it to 0, this may produce out-of-memory conditions)
vm.vfs_cache_pressure=50

# This action will speed up your boot and shutdown, because one less module is loaded. Additionally disabling watchdog timers increases performance and lowers power consumption
# Disable NMI watchdog
#kernel.nmi_watchdog = 0

# Contains, as a percentage of total available memory that contains free pages and reclaimable
# pages, the number of pages at which a process which is generating disk writes will itself start
# writing out dirty data (Default is 20).
vm.dirty_ratio = 5

# Contains, as a percentage of total available memory that contains free pages and reclaimable
# pages, the number of pages at which the background kernel flusher threads will start writing out
# dirty data (Default is 10).
vm.dirty_background_ratio = 5

# This tunable is used to define when dirty data is old enough to be eligible for writeout by the
# kernel flusher threads.  It is expressed in 100'ths of a second.  Data which has been dirty
# in-memory for longer than this interval will be written out next time a flusher thread wakes up
# (Default is 3000).
#vm.dirty_expire_centisecs = 3000

# The kernel flusher threads will periodically wake up and write old data out to disk.  This
# tunable expresses the interval between those wakeups, in 100'ths of a second (Default is 500).
vm.dirty_writeback_centisecs = 1500

# Enable the sysctl setting kernel.unprivileged_userns_clone to allow normal users to run unprivileged containers.
kernel.unprivileged_userns_clone=1

# To hide any kernel messages from the console
kernel.printk = 3 3 3 3

# Restricting access to kernel logs
kernel.dmesg_restrict = 1

# Restricting access to kernel pointers in the proc filesystem
kernel.kptr_restrict = 2

# Disable Kexec, which allows replacing the current running kernel. 
kernel.kexec_load_disabled = 1

# Increasing the size of the receive queue.
# The received frames will be stored in this queue after taking them from the ring buffer on the network card.
# Increasing this value for high speed cards may help prevent losing packets: 
net.core.netdev_max_backlog = 16384

# Increase the maximum connections
#The upper limit on how many connections the kernel will accept (default 128): 
net.core.somaxconn = 8192

# Increase the memory dedicated to the network interfaces
# The default the Linux network stack is not configured for high speed large file transfer across WAN links (i.e. handle more network packets) and setting the correct values may save memory resources: 
net.core.rmem_default = 1048576
net.core.rmem_max = 16777216
net.core.wmem_default = 1048576
net.core.wmem_max = 16777216
net.core.optmem_max = 65536
net.ipv4.tcp_rmem = 4096 1048576 2097152
net.ipv4.tcp_wmem = 4096 65536 16777216
net.ipv4.udp_rmem_min = 8192
net.ipv4.udp_wmem_min = 8192

# Enable TCP Fast Open
# TCP Fast Open is an extension to the transmission control protocol (TCP) that helps reduce network latency
# by enabling data to be exchanged during the sender’s initial TCP SYN [3]. 
# Using the value 3 instead of the default 1 allows TCP Fast Open for both incoming and outgoing connections: 
net.ipv4.tcp_fastopen = 3

# Enable BBR
# The BBR congestion control algorithm can help achieve higher bandwidths and lower latencies for internet traffic
net.core.default_qdisc = cake
net.ipv4.tcp_congestion_control = bbr

# TCP SYN cookie protection
# Helps protect against SYN flood attacks. Only kicks in when net.ipv4.tcp_max_syn_backlog is reached: 
net.ipv4.tcp_syncookies = 1

# Protect against tcp time-wait assassination hazards, drop RST packets for sockets in the time-wait state. Not widely supported outside of Linux, but conforms to RFC: 
net.ipv4.tcp_rfc1337 = 1

# By enabling reverse path filtering, the kernel will do source validation of the packets received from all the interfaces on the machine. This can protect from attackers that are using IP spoofing methods to do harm. 
net.ipv4.conf.default.rp_filter = 1
net.ipv4.conf.all.rp_filter = 1

# Disable ICMP redirects
net.ipv4.conf.all.accept_redirects = 0
net.ipv4.conf.default.accept_redirects = 0
net.ipv4.conf.all.secure_redirects = 0
net.ipv4.conf.default.secure_redirects = 0
net.ipv6.conf.all.accept_redirects = 0
net.ipv6.conf.default.accept_redirects = 0
net.ipv4.conf.all.send_redirects = 0
net.ipv4.conf.default.send_redirects = 0

# To use the new FQ-PIE Queue Discipline (>= Linux 5.6) in systems with systemd (>= 217), will need to replace the default fq_codel. 
net.core.default_qdisc = fq_pie
EOF
cat << EOF > /etc/nftables.conf
flush ruleset

table ip filter {
  chain DOCKER-USER {
    mark set 1
  }
}

table inet my_table {
	chain my_input {
		type filter hook input priority 0; policy drop;

		iif lo accept comment "Accept any localhost traffic"
		ct state invalid drop comment "Drop invalid connections"
		
		meta l4proto icmp icmp type echo-request limit rate over 10/second burst 4 packets drop comment "No ping floods"
		meta l4proto ipv6-icmp icmpv6 type echo-request limit rate over 10/second burst 4 packets drop comment "No ping floods"

		ct state established,related accept comment "Accept traffic originated from us"

		meta l4proto ipv6-icmp icmpv6 type { destination-unreachable, packet-too-big, time-exceeded, parameter-problem, mld-listener-query, mld-listener-report, mld-listener-reduction, nd-router-solicit, nd-router-advert, nd-neighbor-solicit, nd-neighbor-advert, ind-neighbor-solicit, ind-neighbor-advert, mld2-listener-report } accept comment "Accept ICMPv6"
		meta l4proto ipv6-icmp icmpv6 type { destination-unreachable, packet-too-big, time-exceeded, parameter-problem, mld-listener-query, mld-listener-report, mld-listener-reduction, nd-router-solicit, nd-router-advert, nd-neighbor-solicit, nd-neighbor-advert, ind-neighbor-solicit, ind-neighbor-advert, mld2-listener-report } accept comment "Accept ICMPv6"
		meta l4proto icmp icmp type { destination-unreachable, router-solicitation, router-advertisement, time-exceeded, parameter-problem } accept comment "Accept ICMP"
		ip protocol igmp accept comment "Accept IGMP"

		tcp dport ssh ct state new limit rate 15/minute accept comment "Avoid brute force on SSH"

		udp dport mdns ip6 daddr ff02::fb accept comment "Accept mDNS"
		udp dport mdns ip daddr 224.0.0.251 accept comment "Accept mDNS"

		udp sport 1900 udp dport >= 1024 ip6 saddr { fd00::/8, fe80::/10 } meta pkttype unicast limit rate 4/second burst 20 packets accept comment "Accept UPnP IGD port mapping reply"
		udp sport 1900 udp dport >= 1024 ip saddr { 10.0.0.0/8, 172.16.0.0/12, 192.168.0.0/16, 169.254.0.0/16 } meta pkttype unicast limit rate 4/second burst 20 packets accept comment "Accept UPnP IGD port mapping reply"

		udp sport netbios-ns udp dport >= 1024 meta pkttype unicast ip6 saddr { fd00::/8, fe80::/10 } accept comment "Accept Samba Workgroup browsing replies"
		udp sport netbios-ns udp dport >= 1024 meta pkttype unicast ip saddr { 10.0.0.0/8, 172.16.0.0/12, 192.168.0.0/16, 169.254.0.0/16 } accept comment "Accept Samba Workgroup browsing replies"

		counter comment "Count any other traffic"
	}

	chain my_forward {
		type filter hook forward priority security; policy drop;
  		mark 1 accept
		# Drop everything forwarded to that's not from docker us. We do not forward. That is routers job.
	}

	chain my_output {
		type filter hook output priority 0; policy accept;
		# Accept every outbound connection
	}

}

table inet dev {
    set blackhole {
        type ipv4_addr;
        flags dynamic, timeout;
        size 65536;
    }

    chain input {
        ct state new tcp dport 443 \
                meter flood size 128000 { ip saddr timeout 10s limit rate over 10/second } \
                add @blackhole { ip saddr timeout 1m }

        ip saddr @blackhole counter drop
    }
}
EOF
cat << EOF > /etc/sshguard.conf
# Full path to backend executable (required, no default)
BACKEND="/usr/lib/sshguard/sshg-fw-nft-sets"

# Log reader command (optional, no default)
LOGREADER="LANG=C /usr/bin/journalctl -afb -p info -n1 -t sshd -t vsftpd -o cat"

# How many problematic attempts trigger a block
THRESHOLD=20
# Blocks last at least 180 seconds
BLOCK_TIME=180
# The attackers are remembered for up to 3600 seconds
DETECTION_TIME=3600

# Blacklist threshold and file name
BLACKLIST_FILE=100:/var/db/sshguard/blacklist.db

# IPv6 subnet size to block. Defaults to a single address, CIDR notation. (optional, default to 128)
IPV6_SUBNET=64
# IPv4 subnet size to block. Defaults to a single address, CIDR notation. (optional, default to 32)
IPV4_SUBNET=24
EOF
cat << EOF > /etc/profile.d/shell-timeout.sh
TMOUT="\$(( 60*30 ))";
[ -z "\$DISPLAY" ] && export TMOUT;
case \$( /usr/bin/tty ) in
	/dev/tty[0-9]*) export TMOUT;;
esac
EOF
cat <<EOF > /etc/pam.d/login
#%PAM-1.0
 
auth       required     pam_securetty.so
auth       requisite    pam_nologin.so
auth       include      system-local-login
auth       optional     pam_gnome_keyring.so
account    include      system-local-login
session    include      system-local-login
session    optional     pam_gnome_keyring.so auto_start
EOF
cat <<EOF > /etc/pam.d/passwd
#%PAM-1.0

#password	required	pam_cracklib.so difok=2 minlen=8 dcredit=2 ocredit=2 retry=3
#password	required	pam_unix.so sha512 shadow use_authtok
password	required	pam_unix.so sha512 shadow nullok
password	optional	pam_gnome_keyring.so
EOF
cat << EOF > /etc/zsh/zshenv
export ZDOTDIR=$HOME/.config/zsh
export HISTFILE="$XDG_DATA_HOME"/zsh/history
EOF
su -l postgres
initdb --locale=en_US.UTF-8 -E UTF8 -D /var/lib/postgres/data && \
exit

cat << EOF > /var/lib/postgres/data/postgresql.conf
stats_temp_directory = '/run/postgresql'
EOF
su $USER
cd ~  && \
git clone https://aur.archlinux.org/yay.git && \
cd yay && \
makepkg -si && \
cd .. && \
sudo rm -dR yay
yay --noremovemake --nodiffmenu -S shim-signed && \
sudo refind-install --shim /usr/share/shim-signed/shimx64.efi --localkeys && \
sudo sbsign --key /etc/refind.d/keys/refind_local.key --cert /etc/refind.d/keys/refind_local.crt --output /boot/vmlinuz-linux /boot/vmlinuz-linux
rustup default stable && \
yay --noremovemake --nodiffmenu --batchinstall -S otf-san-francisco fedora-firefox-wayland-bin \
    otf-san-francisco pamac-aur starship-bin firefox-extension-amazon-container \
    gst-plugin-libde265 firefox-extension-privacybadger poweralertd zoxide-bin \
    firefox-extension-https-everywhere firefox-extension-facebook-container wob \
    firefox-extension-containerise ananicy-git lastpass nwg-launchers persway \
    neovim-nightly-git swaylock-effects-git lazygit-bin grimshot memavaild \
    prelockd nohang-git auto-cpufreq-git otf-nerd-fonts-monacob-mono refind-btrfs \
    bat-extras-git opennic-up ttf-wps-office-fonts wps-office wps-office-mime \
    neovim-remote git-delta-bin  git-journal just gitui-bin procs-bin smug \
    nushell-bin
yay --noremovemake --nodiffmenu --editmenu -S linux-xanmod-cacule linux-xanmod-cacule-headers
export PATH=/usr/bin/ && yay -S nerd-fonts-jetbrains-mono
# Now is a good time to install dotfiles
# Example 1 (bare git repo)
# git clone --bare https://github.com/Th3Whit3Wolf/.dots.git $HOME/.dots
# git --git-dir=$HOME/.dots/ --work-tree=$HOME checkout
# Example 2 (chezmoi)
# chezmoi init https://github.com/Th3Whit3Wolf/dots.git
exit
mkdir /boot/EFI/refind/themes  && \
git clone https://github.com/dheishman/refind-dreary.git /boot/EFI/refind/themes/refind-dreary-git && \
mv /boot/EFI/refind/themes/refind-dreary-git/highres /boot/EFI/refind/themes/refind-dreary && \
rm -dR /boot/EFI/refind/themes/refind-dreary-git
sed -i 's/#resolution 3/resolution 1920 1080/' /boot/EFI/refind/refind.conf && \
sed -i 's/#use_graphics_for osx,linux/use_graphics_for linux/' /boot/EFI/refind/refind.conf && \
sed -i 's/#scanfor internal,external,optical,manual/scanfor manual,external/' /boot/EFI/refind/refind.conf
sed -i 's/^hideui.*/hideui singleuser,hints,arrows,badges/' /boot/EFI/refind/themes/refind-dreary/theme.conf
cat << EOF >> /boot/EFI/refind/refind.conf

menuentry "Arch Linux" {
    icon     /EFI/refind/themes/refind-dreary/icons/os_arch.png
    volume   "Arch Linux"
    loader   /vmlinuz-linux
    initrd   /initramfs-linux.img
    options  "rd.luks.name=$(blkid /dev/nvme0n1p2 | cut -d " " -f2 | cut -d '=' -f2 | sed 's/\"//g')=crypt root=/dev/mapper/crypt rootflags=subvol=@ rw quiet nmi_watchdog=0 kernel.unprivileged_userns_clone=0 net.core.bpf_jit_harden=2 apparmor=1 lsm=lockdown,yama,apparmor systemd.unified_cgroup_hierarchy=1 add_efi_memmap initrd=\amd-ucode.img"
    submenuentry "Boot - terminal" {
        add_options "systemd.unit=multi-user.target"
    }
}

menuentry "Arch Linux - Low Latency" {
    icon     /EFI/refind/themes/refind-dreary/icons/os_arch.png
    volume   "Arch Linux"
    loader   /vmlinuz-linux-xanmod-cacule
    initrd   /initramfs-linux-xanmod-cacule.img
    options  "rd.luks.name=$(blkid /dev/nvme0n1p2 | cut -d " " -f2 | cut -d '=' -f2 | sed 's/\"//g')=crypt root=/dev/mapper/crypt rootflags=subvol=@ rw quiet nmi_watchdog=0 kernel.unprivileged_userns_clone=0 net.core.bpf_jit_harden=2 apparmor=1 lsm=lockdown,yama,apparmor systemd.unified_cgroup_hierarchy=1 add_efi_memmap initrd=\amd-ucode.img"
    submenuentry "Boot - terminal" {
        add_options "systemd.unit=multi-user.target"
    }
}

include themes/refind-dreary/theme.conf
EOF
sed -i 's/^count.*/count = "inf"/' /etc/refind-btrfs.conf
sed -i 's/^include_sub_menus.*/include_sub_menus = true/' /etc/refind-btrfs.conf
sudo pacman --noconfirm -S snap-pac
cat << EOF >> /home/$USER/init.sh
sudo umount /.snapshots
sudo rm -r /.snapshots
sudo snapper -c root create-config /
sudo mount -a
sudo chmod 750 -R /.snapshots
sudo chmod a+rx /.snapshots
sudo chown :wheel /.snapshots
sudo snapper -c root create --description "Fresh Install"
sudo sed -i 's/^TIMELINE_MIN_AGE.*/TIMELINE_MIN_AGE="1800"/' /etc/snapper/configs/root && \
sudo sed -i 's/^TIMELINE_LIMIT_HOURLY.*/TIMELINE_LIMIT_HOURLY="0"/' /etc/snapper/configs/root && \
sudo sed -i 's/^TIMELINE_LIMIT_DAILY.*/TIMELINE_LIMIT_DAILY="7"/' /etc/snapper/configs/root && \
sudo sed -i 's/^TIMELINE_LIMIT_WEEKLY.*/TIMELINE_LIMIT_WEEKLY="0"/' /etc/snapper/configs/root && \
sudo sed -i 's/^TIMELINE_LIMIT_MONTHLY.*/TIMELINE_LIMIT_MONTHLY="0"/' /etc/snapper/configs/root && \
sudo sed -i 's/^TIMELINE_LIMIT_YEARLY.*/TIMELINE_LIMIT_YEARLY="0"/' /etc/snapper/configs/root
sudo systemctl enable --now snapper-timeline.timer snapper-cleanup.timer
sudo systemctl disable --now systemd-timesyncd.service
sudo systemctl mask systemd-rfkill.socket systemd-rfkill.service
sudo systemctl enable --now NetworkManager 
sudo systemctl enable --now NetworkManager-wait-online
sudo systemctl enable --now NetworkManager-dispatcher
sudo systemctl enable --now nftables
sudo systemctl enable --now opennic-up.timer
sudo systemctl enable --now sshd 
sudo systemctl enable --now chronyd
sudo systemctl enable --now reflector
sudo systemctl enable --now apparmor 
sudo systemctl enable --now sshguard
sudo systemctl enable --now tlp 
sudo systemctl enable --now memavaild 
sudo systemctl enable --now haveged 
sudo systemctl enable --now irqbalance 
sudo systemctl enable --now prelockd 
sudo systemctl enable --now systemd-swap 
sudo systemctl enable --now nohang-desktop 
sudo systemctl enable --now auto-cpufreq 
sudo systemctl enable --now dbus-broker
sudo systemctl enable --now postgresql
sudo systemctl enable --now refind-btrfs
systemctl --user start psd
sudo systemctl enable --now gdm
rm /home/$USER/init.sh
EOF
chown $USER /home/$USER/init.sh
exit
umount -R /mnt && \
reboot