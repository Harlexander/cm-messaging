import { CKEditor } from '@ckeditor/ckeditor5-react';
import {
  ClassicEditor,
  Bold,
  Essentials,
  Heading,
  Indent,
  IndentBlock,
  Italic,
  Link,
  List,
  MediaEmbed,
  Paragraph,
  Table,
  Undo
} from 'ckeditor5';

import 'ckeditor5/ckeditor5.css';

export default function TextEditor({ setMessage }: { setMessage: (message: string) => void }) {
  return (
   <div className='border border-gray-300 rounded-md p-2 w-full text-black'>
     <CKEditor
      onChange={(event, editor) => {
        const data = editor.getData();
        setMessage(data);
      }}
      editor={ ClassicEditor }
      
      config={ {
        licenseKey: 'eyJhbGciOiJFUzI1NiJ9.eyJleHAiOjE3NDI0Mjg3OTksImp0aSI6ImUzNGQyMGQwLTBkNDctNDMyMi1hNTgyLTJmYjBlZWQ0NTdmYyIsInVzYWdlRW5kcG9pbnQiOiJodHRwczovL3Byb3h5LWV2ZW50LmNrZWRpdG9yLmNvbSIsImRpc3RyaWJ1dGlvbkNoYW5uZWwiOlsiY2xvdWQiLCJkcnVwYWwiLCJzaCJdLCJ3aGl0ZUxhYmVsIjp0cnVlLCJsaWNlbnNlVHlwZSI6InRyaWFsIiwiZmVhdHVyZXMiOlsiKiJdLCJ2YyI6IjhkMmJhYzA3In0.5S7sIHjw6exCh1hcVw73yzJHb94FLt5tuApo5QVNK1sPB1Q-efCZtcNFK3cKd0O-epsSOeA2Lmru8oJq0NDUoQ',
        toolbar: [
          // 'undo', 'redo', '|',
          'heading', '|', 'bold', 'italic', '|',
          'link', 'mediaEmbed', '|',
          'bulletedList', 'numberedList', 'indent', 'outdent'
        ],
        plugins: [
          Bold,
          Essentials,
          Heading,
          Indent,
          IndentBlock,
          Italic,
          Link,
          List,
          MediaEmbed,
          Paragraph,
          Table,
          Undo
        ],
        initialData: '<h1>Hello from CKEditor 5!</h1>',
      } }
    />
   </div>
  );
}
