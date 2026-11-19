"""
Build official brand banners for WhatsApp Promo banner
Produces 4 banners (560x125, RGB PNG):
- img/banner_wa_kopken.png
- img/banner_wa_tomoro.png
- img/banner_wa_fore.png
- img/banner_wa_jiwa.png
And synchronizes copies to assets/img/
"""

import os
import shutil
import numpy as np
from PIL import Image, ImageFilter

def make_banner(bg_base, cup_img, target_h=104, center_x=410, center_y=63):
    banner = bg_base.copy()
    
    # Resize cup keeping aspect ratio
    w, h = cup_img.size
    new_h = target_h
    new_w = int(w * (new_h / h))
    cup_resized = cup_img.resize((new_w, new_h), Image.Resampling.LANCZOS)
    
    # Drop shadow
    alpha = cup_resized.split()[3]
    shadow = Image.new('RGBA', (new_w + 24, new_h + 24), (0, 0, 0, 0))
    shadow_color = Image.new('RGBA', (new_w, new_h), (0, 20, 60, 110))
    shadow.paste(shadow_color, (12, 12), mask=alpha)
    shadow = shadow.filter(ImageFilter.GaussianBlur(radius=5))
    
    pos_x = center_x - new_w // 2
    pos_y = center_y - new_h // 2
    
    banner.alpha_composite(shadow, (pos_x - 12 + 3, pos_y - 12 + 4))
    banner.alpha_composite(cup_resized, (pos_x, pos_y))
    
    return banner.convert('RGB')

def main():
    root = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
    os.chdir(root)
    
    bg_base = Image.open('scratch_banner_bg.png').convert('RGBA')
    
    # 1. Kopi Kenangan: Authentic promo banner
    shutil.copyfile('img/banner_wa_promo.png', 'img/banner_wa_kopken.png')
    
    # 2. Tomoro Coffee
    t_im = Image.open('img/menu/tomoro/tomoroarenlatte.webp').convert('RGBA')
    t_arr = np.array(t_im)
    white_mask = (t_arr[:, :, 0] > 245) & (t_arr[:, :, 1] > 245) & (t_arr[:, :, 2] > 245)
    t_arr[white_mask, 3] = 0
    t_clean = Image.fromarray(t_arr).crop((90, 35, 405, 465))
    b_tomoro = make_banner(bg_base, t_clean, target_h=104, center_x=410, center_y=63)
    b_tomoro.save('img/banner_wa_tomoro.png', format='PNG', optimize=True)
    
    # 3. Fore Coffee
    f_im = Image.open('scratch_fore_cup.jpg').convert('RGBA')
    f_crop = f_im.crop((120, 55, 380, 465))
    f_arr = np.array(f_crop)
    bg_fore = np.array([251, 248, 242], dtype=float)
    diff = np.sqrt(np.sum((f_arr[:, :, :3].astype(float) - bg_fore)**2, axis=-1))
    alpha_fore = np.clip((diff - 12) / 15 * 255, 0, 255).astype(np.uint8)
    f_arr[:, :, 3] = alpha_fore
    f_clean = Image.fromarray(f_arr)
    f_bbox = Image.fromarray(alpha_fore).point(lambda p: 255 if p > 20 else 0).getbbox()
    if f_bbox:
        f_clean = f_clean.crop(f_bbox)
    b_fore = make_banner(bg_base, f_clean, target_h=106, center_x=410, center_y=63)
    b_fore.save('img/banner_wa_fore.png', format='PNG', optimize=True)
    
    # 4. Janji Jiwa
    j_im = Image.open('scratch_jiwa_cup.webp').convert('RGBA')
    j_clean = j_im.crop(j_im.getbbox())
    b_jiwa = make_banner(bg_base, j_clean, target_h=104, center_x=410, center_y=63)
    b_jiwa.save('img/banner_wa_jiwa.png', format='PNG', optimize=True)
    
    # Sync all to assets/img/
    os.makedirs('assets/img', exist_ok=True)
    for b in ['kopken', 'tomoro', 'fore', 'jiwa']:
        src = f'img/banner_wa_{b}.png'
        dst = f'assets/img/banner_wa_{b}.png'
        shutil.copyfile(src, dst)
        print(f'Synced {src} -> {dst}')
    shutil.copyfile('img/banner_wa_promo.png', 'assets/img/banner_wa_promo.png')
    
    print('All brand banners generated and synced successfully!')

if __name__ == '__main__':
    main()
