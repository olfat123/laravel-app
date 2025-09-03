import { useEffect } from "react";
import { useState } from "react";

export default function Carousel({ images }) {
    const [selectedImage, setSelectedImage] = useState(images[0]);
    useEffect(() => {
        setSelectedImage(images[0]);
    }, [images]);
  return (
    <>
    <div className="flex items-start gap-8">
        <div className="flex flex-col gap-2 items-center py-2">
            {images.map((image, index) => (
                <button  onClick={ev => 
                    setSelectedImage(image)}
                    key={image.id} 
                    className="{
                    'border-2 ' + 
                    (selectedImage.id === image.id ? 
                    'border-blue-500' : 'hover:border-blue-500')
                    }">
                    <img src={image.thumb} alt={`Image ${index}`} className="w-[50px]" />
                </button>
            ))}
        </div>
        <div className="carousel w-full">
            <div className="carousel-item w-full">
                <img 
                src={selectedImage.large} 
                alt="Carousel Image" 
                className="w-full" />
            </div>
        </div>
    </div>
    </>
  );
}