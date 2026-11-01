# استخدام صورة PHP رسمية مع Apache
FROM php:8.2-apache

# تثبيت الحزم الأساسية ونظام التشغيل اللازم لـ Laravel
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip

# تنظيف الكاش الخاص بالحزم لتخفيف حجم الصورة
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# تثبيت الـ PHP Extensions المطلوبة للارافيل
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# تحميل وتثبيت Composer (مدير حزم PHP)
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# تحديد مجلد العمل داخل السيرفر
WORKDIR /var/www/html

# نسخ ملفات المشروع بالكامل إلى الحاوية (Container)
COPY . .

# ضبط مسار الـ Document Root الخاص بـ Apache ليوجه للـ public folder في لارافيل
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf

# إعطاء صلاحيات الكتابة لمجلدات التخزين والـ Cache
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# تفعيل الـ Apache Mod Rewrite الخاص بالروابط (Routing)
RUN a2enmod rewrite

# منفذ التشغيل
EXPOSE 80

# أمر التشغيل النهائي لـ Apache
CMD php artisan config:cache && php artisan route:cache && apache2-foreground