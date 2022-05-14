cryptsetup luksFormat --perf-no_read_workqueue --perf-no_write_workqueue --type luks2 --cipher aes-xts-plain64 --key-size 512 --iter-time 2000 --pbkdf argon2id --hash sha3-512 /dev/sda2
cryptsetup --allow-discards --perf-no_read_workqueue --perf-no_write_workqueue --persistent open /dev/sda2 crypt
mkfs.vfat -F32 -n "EFI" /dev/sda1
mkfs.btrfs -L Arch -f /dev/mapper/crypt
mount /dev/mapper/crypt /mnt
btrfs sub create /mnt/@ && \
btrfs sub create /mnt/@home && \
btrfs sub create /mnt/@abs && \
btrfs sub create /mnt/@tmp && \
btrfs sub create /mnt/@srv && \
btrfs sub create /mnt/@snapshots && \
btrfs sub create /mnt/@btrfs && \
btrfs sub create /mnt/@log && \
btrfs sub create /mnt/@cache
umount /mnt
mount -o noatime,compress-force=zstd,commit=120,space_cache=v2,ssd,discard=async,autodefrag,subvol=@ /dev/mapper/crypt /mnt
mkdir -p /mnt/{boot,home,var/cache,var/log,.snapshots,btrfs,var/tmp,var/abs,srv}
mount -o noatime,compress-force=zstd,commit=120,space_cache=v2,ssd,discard=async,autodefrag,subvol=@home /dev/mapper/crypt /mnt/home  && \
mount -o nodev,nosuid,noexec,noatime,compress-force=zstd,commit=120,space_cache=v2,ssd,discard=async,autodefrag,subvol=@abs /dev/mapper/crypt /mnt/var/abs && \
mount -o nodev,nosuid,noexec,noatime,compress-force=zstd,commit=120,space_cache=v2,ssd,discard=async,autodefrag,subvol=@tmp /dev/mapper/crypt /mnt/var/tmp && \
mount -o noatime,compress-force=zstd,commit=120,space_cache=v2,ssd,discard=async,autodefrag,subvol=@srv /dev/mapper/crypt /mnt/srv && \
mount -o nodev,nosuid,noexec,noatime,compress-force=zstd,commit=120,space_cache=v2,ssd,discard=async,autodefrag,subvol=@log /dev/mapper/crypt /mnt/var/log && \
mount -o nodev,nosuid,noexec,noatime,compress-force=zstd,commit=120,space_cache=v2,ssd,discard=async,autodefrag,subvol=@cache /dev/mapper/crypt /mnt/var/cache && \
mount -o noatime,compress-force=zstd,commit=120,space_cache=v2,ssd,discard=async,autodefrag,subvol=@snapshots /dev/mapper/crypt /mnt/.snapshots && \
mount -o noatime,compress-force=zstd,commit=120,space_cache=v2,ssd,discard=async,autodefrag,subvolid=5 /dev/mapper/crypt /mnt/btrfs
mkdir -p /mnt/var/lib/{docker,machines,mysql,postgres} && \
chattr +C /mnt/var/lib/{docker,machines,mysql,postgres}
mount -o nodev,nosuid,noexec /dev/sda1 /mnt/boot
pacstrap /mnt base base-devel linux linux-firmware amd-ucode btrfs-progs git go \
    kanshi zstd iwd networkmanager mesa vulkan-radeon libva-mesa-driver openssh \
    mesa-vdpau xf86-video-amdgpu docker libvirt qemu refind rustup wl-clipboard \
    zsh sshguard npm bc ripgrep bat tokei hyperfine rust-analyzer xdg-user-dirs \
    systemd-swap pigz pbzip2 snapper chrony noto-fonts a52dec faac iptables-nft \
    tlp faad2 flac jasper grim libdca libdv libmad libmpeg2 libtheora libvorbis \
    waybar wavpack xvidcore libde265 gstreamer gst-libav gst-plugins-bad breeze \
    gst-plugins-base gst-plugins-good gst-plugins-ugly gstreamer-vaapi seahorse \
    sway lollypop alacritty wofi polkit-gnome mako slurp xdg-desktop-portal-wlr \
    gvfs libxv libsecret gnome-keyring nautilus nautilus-image-converter gdm fd \
    xarchiver arj cpio lha udiskie nautilus-share nautilus-sendto imv mpv lrzip \
    unrar zip chezmoi powertop brightnessctl lastpass-cli sbsigntools x264 lzip \
    xorg-xwayland apparmor ttf-roboto ttf-roboto-mono ttf-dejavu ttf-liberation \
    ttf-fira-code ttf-hanazono ttf-fira-mono seahorse-nautilus exa ttf-opensans \
    pulseaudio lzop p7zip ttf-hack noto-fonts noto-fonts-emoji ttf-font-awesome \
    ttf-droid adobe-source-code-pro-fonts firefox-decentraleyes libva-utils man \
    firefox-dark-reader lame network-manager-applet unarj blueman yarn npm code \
    firefox-ublock-origin irqbalance swayidle haveged profile-sync-daemon shfmt \
    compsize pipewire-pulse pipewire-jack pipewire-alsa gnome-boxes wf-recorder \
    dbus-broker wireplumber skim youtube-dl nftables python-nautilus celluloid \
    entr reflector postgresql tmux gnome-podcasts
genfstab -U /mnt > /mnt/etc/fstab
timedatectl set-ntp true
cp /etc/zsh/zprofile /mnt/root/.zprofile && \
cp /etc/zsh/zshrc /mnt/root/.zshrc
cp /etc/pacman.d/mirrorlist /mnt/etc/pacman.d/mirrorlist
arch-chroot /mnt /bin/zsh
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
sed -i 's/load-module module-suspend-on-idle/#load-module module-suspend-on-idle/' /etc/pulse/default.pa
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
# set scheduler for NVMe
ACTION=="add|change", KERNEL=="nvme[0-9]*", ATTR{queue/scheduler}="none"
# set scheduler for SSD and eMMC
ACTION=="add|change", KERNEL=="sd[a-z]|mmcblk[0-9]*", ATTR{queue/rotational}=="0", ATTR{queue/scheduler}="mq-deadline"
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
