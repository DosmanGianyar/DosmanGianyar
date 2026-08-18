## Flutter Wrapper & Core
-keep class io.flutter.** { *; }
-keep class com.google.** { *; }
-dontwarn io.flutter.embedding.**

## Android Support & Jetpack
-keep class androidx.** { *; }
-dontwarn androidx.**

## Plugins: Geolocator, Camera, ImagePicker, Dio, SecureStorage
-keep class com.baseflow.** { *; }
-keep class io.flutter.plugins.** { *; }
-keep class com.it_ne.flutter_secure_storage.** { *; }

## Reflection and Serializers
-keepattributes *Annotation*,Signature,InnerClasses,EnclosingMethod
-dontwarn sun.misc.Unsafe

## R8 Proguard warnings suppression
-dontwarn com.google.android.play.core.**
-dontwarn io.flutter.app.**
-dontwarn **
-ignorewarnings

