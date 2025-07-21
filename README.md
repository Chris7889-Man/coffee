Menuju halaman Awal

http://localhost/coffee/iklan.php

http://coffee.test/iklan.php


Untuk login Staff :
   Email    : ibnuha010@gmail.com
   Password : 121212


   Email    :	abc@gmail.com 
   Password :  rusdi123
   username :  rusdi

   Email    : 	efg@gmail.com
   Password :  12345678
   username :  calu


Untuk Admin 
   Email    : fadilcs@gmail.com
   Password : 12345678
   username : fadil

   Email    : githanona@gmail.com
   Password : 12345678
   username : nona

Untuk Super Admin
   Email    : admin@cinema221065.com
   Password : admin123 
   username : admin 




1. kodingan ini berleasi masuk dulu ke manage_menu.php / kelola menu untuk membuat nama-menu dan harganya, admin

2. dan di mana manage_orders.php sudah bisa mengelola Pesanan atau tambah order dan sudah memiliki pilihan  opsi pesanan yang telah dibuat dari kelola menu staff.

3. disini memiliki situs untuk login pada staf dan owner

M S I@MSI MINGW64 /c/laragon/www/coffee (main)
$ git status
On branch main
Your branch is up to date with 'origin/main'.

Changes not staged for commit:
  (use "git add/rm <file>..." to update what will be committed)
  (use "git restore <file>..." to discard changes in working directory)
        modified:   admin/dashboard.php
        modified:   admin/detail_pesanan.php
        modified:   admin/edit_menu.php
        modified:   admin/edit_orders.php
        modified:   admin/manage_admin.php
        modified:   admin/manage_menu.php
        modified:   admin/manage_orders.php
        modified:   admin/menu.php
        modified:   admin/tambah_menu.php
        modified:   admin/tambah_orders.php
        modified:   classes/Pesanan.php
        modified:   coffee.sql
        modified:   config/database.php
        modified:   dokumentasi.md
        modified:   iklan.php
        modified:   karyawan/dashboard.php
        modified:   karyawan/detail_pesanan.php
        modified:   karyawan/index.php
        deleted:    karyawan/laporan_harian.php
        modified:   karyawan/pesan_minuman.php
        modified:   karyawan/proses_pesanan.php
        modified:   karyawan/view_menu.php

Untracked files:
  (use "git add <file>..." to include in what will be committed)
        admin/laporan_harian.php
        admin0/
        assets/admincoffe.jpg
        karyawan/order_pesanan.php

no changes added to commit (use "git add" and/or "git commit -a")

M S I@MSI MINGW64 /c/laragon/www/coffee (main)
$ git add .
warning: in the working copy of 'coffee.sql', LF will be replaced by CRLF the next time Git touches it

M S I@MSI MINGW64 /c/laragon/www/coffee (main)
$ git commit -m 'apdate codingan terbaru'
[main 690cb44] apdate codingan terbaru
 47 files changed, 4852 insertions(+), 640 deletions(-)
 create mode 100644 admin/laporan_harian.php
 create mode 100644 admin0/dashboard.php
 create mode 100644 admin0/data_staff.php
 create mode 100644 admin0/delete_menu.php
 create mode 100644 admin0/delete_orders.php
 create mode 100644 admin0/detail_pesanan.php
 create mode 100644 admin0/edit_menu.php
 create mode 100644 admin0/edit_orders.php
 create mode 100644 admin0/index.php
 create mode 100644 admin0/laporan_harian.php
 create mode 100644 admin0/login.php
 create mode 100644 admin0/logout.php
 create mode 100644 admin0/manage_admin.php
 create mode 100644 admin0/manage_customers.php
 create mode 100644 admin0/manage_menu.php
 create mode 100644 admin0/manage_orders.php
 create mode 100644 admin0/menu.php
 create mode 100644 admin0/order_detail.php
 create mode 100644 admin0/pesanan.php
 create mode 100644 admin0/proses_tambah_staff.php
 create mode 100644 admin0/tambah_menu.php
 create mode 100644 admin0/tambah_orders.php
 create mode 100644 admin0/tambah_staff.php
 create mode 100644 assets/admincoffe.jpg
 delete mode 100644 karyawan/laporan_harian.php
 create mode 100644 karyawan/order_pesanan.php

M S I@MSI MINGW64 /c/laragon/www/coffee (main)
$ git remote -v
origin  https://github.com/Chris7889-Man/coffee.git (fetch)
origin  https://github.com/Chris7889-Man/coffee.git (push)

M S I@MSI MINGW64 /c/laragon/www/coffee (main)
$ git branch
* main

M S I@MSI MINGW64 /c/laragon/www/coffee (main)
$ git push origin main
Enumerating objects: 60, done.
Counting objects: 100% (60/60), done.
Delta compression using up to 12 threads
Compressing objects: 100% (34/34), done.
Writing objects: 100% (35/35), 66.24 KiB | 7.36 MiB/s, done.
Total 35 (delta 21), reused 0 (delta 0), pack-reused 0 (from 0)
remote: Resolving deltas: 100% (21/21), completed with 16 local objects.
To https://github.com/Chris7889-Man/coffee.git
   4807583..690cb44  main -> main

M S I@MSI MINGW64 /c/laragon/www/coffee (main)
$ exit

<------cara memanggil data yang hilang pada saat melakukan penyimpanan full ke git hub------>
M S I@MSI MINGW64 /c/laragon/www/coffee (main|REBASE 1/2)
$ git reflog
4807583 (HEAD) HEAD@{0}: reset: moving to 4807583
690cb44 HEAD@{1}: reset: moving to 690cb44
a77cf92 (origin/main, tmp) HEAD@{2}: rebase (start): checkout tmp
5b63b39 (main) HEAD@{3}: commit: update codingan terbaru
02fb5bd HEAD@{4}: commit: update di sore hari
690cb44 HEAD@{5}: commit: apdate codingan terbaru
4807583 (HEAD) HEAD@{6}: Branch: renamed refs/heads/main to refs/heads/main
4807583 (HEAD) HEAD@{8}: commit (initial): first commit

M S I@MSI MINGW64 /c/laragon/www/coffee (main|REBASE 1/2)
$ git reset --hard 4807583
HEAD is now at 4807583 first commit

M S I@MSI MINGW64 /c/laragon/www/coffee (main|REBASE 1/2)
$ git log -1
commit 4807583e64710f217cde632285944ad4369e360c (HEAD)
Author: Chris7889-Mang <irginiussteven654@gmail.com>
Date:   Fri Jul 18 03:31:23 2025 +0800

    first commit

M S I@MSI MINGW64 /c/laragon/www/coffee (main|REBASE 1/2)
$ git status
interactive rebase in progress; onto a77cf92
Last command done (1 command done):
   pick 02fb5bd update di sore hari
Next command to do (1 remaining command):
   pick 5b63b39 update codingan terbaru
  (use "git rebase --edit-todo" to view and edit)
You are currently editing a commit while rebasing branch 'main' on 'a77cf92'.
  (use "git commit --amend" to amend the current commit)
  (use "git rebase --continue" once you are satisfied with your changes)

nothing to commit, working tree clean

M S I@MSI MINGW64 /c/laragon/www/coffee (main|REBASE 1/2)
$ git reset --hard a77cf92
HEAD is now at a77cf92 Create git gui here update

M S I@MSI MINGW64 /c/laragon/www/coffee (main|REBASE 1/2)
$ git log -1
commit a77cf92123c2de67e19ff1c20f81f099be711505 (HEAD, origin/main, tmp)
Author: Your_Name <irginiussteven654@gmail.com>
Date:   Sat Jul 19 08:23:49 2025 +0800

    Create git gui here update

    untuk melakukan keseluruhan update

M S I@MSI MINGW64 /c/laragon/www/coffee (main|REBASE 1/2)
$ git status
interactive rebase in progress; onto a77cf92
Last command done (1 command done):
   pick 02fb5bd update di sore hari
Next command to do (1 remaining command):
   pick 5b63b39 update codingan terbaru
  (use "git rebase --edit-todo" to view and edit)
You are currently editing a commit while rebasing branch 'main' on 'a77cf92'.
  (use "git commit --amend" to amend the current commit)
  (use "git rebase --continue" once you are satisfied with your changes)

nothing to commit, working tree clean

M S I@MSI MINGW64 /c/laragon/www/coffee (main|REBASE 1/2)
$ git reset reflog
fatal: ambiguous argument 'reflog': unknown revision or path not in the working tree.
Use '--' to separate paths from revisions, like this:
'git <command> [<revision>...] -- [<file>...]'

M S I@MSI MINGW64 /c/laragon/www/coffee (main|REBASE 1/2)
$ git reset --hard 5b63b39
HEAD is now at 5b63b39 update codingan terbaru

M S I@MSI MINGW64 /c/laragon/www/coffee (main|REBASE 1/2)
$ git log -1
commit 5b63b3934d89e816195d73b4f7eebadbc304079b (HEAD, main)
Author: Chris7889-Mang <irginiussteven654@gmail.com>
Date:   Sun Jul 20 10:35:44 2025 +0800

    update codingan terbaru

M S I@MSI MINGW64 /c/laragon/www/coffee (main|REBASE 1/2)
$ git status
interactive rebase in progress; onto a77cf92
Last command done (1 command done):
   pick 02fb5bd update di sore hari
Next command to do (1 remaining command):
   pick 5b63b39 update codingan terbaru
  (use "git rebase --edit-todo" to view and edit)
You are currently editing a commit while rebasing branch 'main' on 'a77cf92'.
  (use "git commit --amend" to amend the current commit)
  (use "git rebase --continue" once you are satisfied with your changes)

nothing to commit, working tree clean

M S I@MSI MINGW64 /c/laragon/www/coffee (main|REBASE 1/2)
$
