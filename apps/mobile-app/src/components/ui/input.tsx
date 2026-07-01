import { cn } from '@/lib/utils';
import { Platform, TextInput } from 'react-native';

function Input({ className, style, ...props }: React.ComponentProps<typeof TextInput> & React.RefAttributes<TextInput>) {
  return (
    <TextInput
      className={cn(
        // Gunakan warna solid (bukan bg-background / CSS var) agar backgroundColor
        // benar-benar ter-apply di TextInput native — var semantic sering transparan.
        'flex h-10 w-full min-w-0 flex-row items-center rounded-md border border-stone-300 bg-white px-3 py-1 text-base leading-5 text-foreground shadow-sm shadow-black/5 dark:border-stone-600 dark:bg-stone-950 sm:h-9',
        props.editable === false &&
        cn(
          'opacity-50',
          Platform.select({ web: 'disabled:pointer-events-none disabled:cursor-not-allowed' })
        ),
        Platform.select({
          web: cn(
            'placeholder:text-muted-foreground selection:bg-primary selection:text-primary-foreground outline-none transition-[color,box-shadow] md:text-sm',
            'focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px]',
            'aria-invalid:ring-destructive/20 dark:aria-invalid:ring-destructive/40 aria-invalid:border-destructive'
          ),
          native: 'placeholder:text-muted-foreground/50',
        }),
        className
      )}
      style={style}
      {...props}
    />
  );
}

export { Input };
