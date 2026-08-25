import type { SVGAttributes } from 'react';

export default function AppLogoIcon(props: SVGAttributes<SVGElement>) {
    return (
        <svg
            {...props}
            viewBox="0 0 40 40"
            xmlns="http://www.w3.org/2000/svg"
            fill="none"
        >
            <rect
                width="40"
                height="40"
                rx="8"
                className="fill-indigo-600 dark:fill-indigo-500"
            />
            <path
                d="M12 10h6.5c5.5 0 9.5 3.5 9.5 10s-4 10-9.5 10H12V10zm6.5 14.5c2.8 0 4.5-2 4.5-4.5s-1.7-4.5-4.5-4.5H18.5V24.5z"
                className="fill-white dark:fill-slate-950"
            />
        </svg>
    );
}
