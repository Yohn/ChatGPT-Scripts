# ChatGPT-Scripts

## Benchmarks
## Find and Replace (`sprintf` vs `strtr` vs `str_replace` vs `preg_replace` vs `bbcode` vs `printf`)

> [!Note]
> I'm kind of leaning to 	`strtr()` what do you guys think?

### PHP 8.3.12

| func() | time() |
| --- | --- |
| sprintf: | 0.236711 seconds |
| strtr: | 0.305862 seconds |
| preg_replace: | 0.400507 seconds |
| bbcode: | 0.458876 seconds |

(didnt run printf in 8.3.12 cause it was too slow)<br>

### PHP 8.3.8<br>

| func() | time() |
| --- | --- |
| strtr | 0.080120 seconds |
| sprintf | 0.117685 seconds |
| preg_replace | 0.133060 seconds |
| bbcode | 0.200995 seconds |
| printf | 0.649218 seconds |

Added str_replace in conjunction with keeping bbcode in there...<br>

| func() | time() |
| --- | --- |
| strtr | 0.096684 seconds |
| sprintf | 0.103677 seconds |
| str_replace | 0.112268 seconds |
| bbcode | 0.185911 seconds |
| preg_replace | 0.236513 seconds |
| printf | 1.242448 seconds |

| func() | time() |
| --- | --- |
| sprintf | 0.083905 seconds |
| strtr | 0.086414 seconds |
| str_replace | 0.115080 seconds |
| preg_replace | 0.132141 seconds |
| bbcode | 0.258569 seconds |
| printf | 0.710810 seconds |

-------

> [!IMPORTANT]
> I'm trying to see how much help this new thing can be.

> [!NOTE]
> 1. Will it actually help programmers? 
> 2. Will it allow non programmers to learn to code?
> 3. Can it actually be helpful?

After a few days of playing with it, heres what I can say:
1. I can see it being helpful with fixing a big where we know what the issue is, and if you can phrase the problem and what you expect the correct way for ChatGPT to understand, then it might be able to help you fix it.
2. No matter how many times you tell it to remember something, and even if you have it set in your settings that you like things done this way, it will revert to doing them the way it likes to.
3. It can be fun seeing some things it will create kind of quickly if you ask it the correct way.
